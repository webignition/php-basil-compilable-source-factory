<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Transpiler\Value;

use webignition\BasilCompilableSourceFactory\Tests\Functional\Transpiler\AbstractTranspilerTest;
use webignition\BasilCompilableSourceFactory\Transpiler\TranspilerInterface;
use webignition\BasilCompilableSourceFactory\Transpiler\Value\ScalarValueTranspiler;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ObjectValueType;
use webignition\BasilModel\Value\ValueInterface;

class ScalarValueTranspilerTest extends AbstractTranspilerTest
{
    protected function createTranspiler(): TranspilerInterface
    {
        return ScalarValueTranspiler::createTranspiler();
    }

    /**
     * @dataProvider transpileDataProvider
     */
    public function testTranspile(
        string $fixture,
        ValueInterface $model,
        MetadataInterface $expectedMetadata,
        callable $resultAssertions,
        array $additionalVariableIdentifiers = [],
        array $additionalSetupStatements = [],
        ?MetadataInterface $additionalMetadata = null
    ) {
        $source = $this->transpiler->transpile($model);

        $this->assertEquals($expectedMetadata, $source->getMetadata());

        $executableCall = $this->createExecutableCallWithReturn(
            $source,
            $fixture,
            array_merge(
                self::VARIABLE_IDENTIFIERS,
                $additionalVariableIdentifiers
            ),
            $additionalSetupStatements,
            [],
            $additionalMetadata
        );

        $resultAssertions(eval($executableCall));
    }

    public function transpileDataProvider(): array
    {
        return [
            'browser property: size' => [
                'fixture' => '/empty.html',
                'model' => new ObjectValue(ObjectValueType::BROWSER_PROPERTY, '$browser.size', 'size'),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                    ]))->withVariableExports(VariablePlaceholderCollection::createCollection([
                        'WEBDRIVER_DIMENSION',
                    ])),
                'resultAssertions' => function ($result) {
                    $this->assertEquals('1200x1100', $result);
                },
                'additionalVariableIdentifiers' => [
                    'WEBDRIVER_DIMENSION' => '$webDriverDimension',
                ],
            ],
            'page property: title' => [
                'fixture' => '/index.html',
                'model' => new ObjectValue(ObjectValueType::PAGE_PROPERTY, '$page.title', 'title'),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                    ])),
                'resultAssertions' => function ($result) {
                    $this->assertEquals('Test fixture web server default document', $result);
                },
            ],
            'page property: url' => [
                'fixture' => '/index.html',
                'model' => new ObjectValue(ObjectValueType::PAGE_PROPERTY, '$page.url', 'url'),
                'expectedMetadata' => (new Metadata())
                    ->withVariableDependencies(VariablePlaceholderCollection::createCollection([
                        VariableNames::PANTHER_CLIENT,
                    ])),
                'resultAssertions' => function ($result) {
                    $this->assertEquals('http://127.0.0.1:9080/index.html', $result);
                },
            ],
        ];
    }
}
