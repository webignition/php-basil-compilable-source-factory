<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Handler\Value;

use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Tests\Functional\Handler\AbstractHandlerTest;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ObjectValueType;
use webignition\BasilModel\Value\ValueInterface;

class ScalarValueHandlerTest extends AbstractHandlerTest
{
    protected function createHandler(): HandlerInterface
    {
        return ScalarValueHandler::createHandler();
    }

    /**
     * @dataProvider transpileDataProvider
     */
    public function testTranspile(
        string $fixture,
        ValueInterface $model,
        callable $resultAssertions,
        array $additionalVariableIdentifiers = []
    ) {
        $statementList = $this->handler->createStatementList($model);

        $additionalSetupStatements = [];

        $executableCall = $this->createExecutableCallForRequestWithReturn(
            $fixture,
            $statementList,
            $additionalSetupStatements,
            $additionalVariableIdentifiers
        );

        $resultAssertions(eval($executableCall));
    }

    public function transpileDataProvider(): array
    {
        return [
            'browser property: size' => [
                'fixture' => '/empty.html',
                'model' => new ObjectValue(ObjectValueType::BROWSER_PROPERTY, '$browser.size', 'size'),
                'resultAssertions' => function ($result) {
                    $this->assertEquals('1200x1100', $result);
                },
                'additionalVariableIdentifiers' => [
                    VariableNames::PANTHER_CLIENT => self::PANTHER_CLIENT_VARIABLE_NAME,
                    'WEBDRIVER_DIMENSION' => '$webDriverDimension',
                ],
            ],
            'page property: title' => [
                'fixture' => '/index.html',
                'model' => new ObjectValue(ObjectValueType::PAGE_PROPERTY, '$page.title', 'title'),
                'resultAssertions' => function ($result) {
                    $this->assertEquals('Test fixture web server default document', $result);
                },
            ],
            'page property: url' => [
                'fixture' => '/index.html',
                'model' => new ObjectValue(ObjectValueType::PAGE_PROPERTY, '$page.url', 'url'),
                'resultAssertions' => function ($result) {
                    $this->assertEquals('http://127.0.0.1:9080/index.html', $result);
                },
            ],
        ];
    }
}