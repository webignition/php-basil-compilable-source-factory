<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Handler\Value;

use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractBrowserTestCase;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilCompilableSourceFactory\Tests\Services\TestRunJob;
use webignition\BasilCompilationSource\Block\Block;
use webignition\BasilCompilationSource\MutableBlockInterface;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ObjectValueType;
use webignition\BasilModel\Value\ValueInterface;

class ScalarValueHandlerTest extends AbstractBrowserTestCase
{
    /**
     * @var ScalarValueHandler
     */
    protected $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = ScalarValueHandler::createHandler();
    }

    /**
     * @dataProvider createSourceDataProvider
     */
    public function testCreateSource(
        string $fixture,
        ValueInterface $model,
        Block $teardownStatements,
        array $additionalVariableIdentifiers = []
    ) {
        $source = $this->handler->handle($model);

        $instrumentedSource = clone $source;
        if ($instrumentedSource instanceof MutableBlockInterface) {
            $instrumentedSource->mutateLastStatement(function ($content) {
                return '$value = ' . $content;
            });
        }

        $classCode = $this->testCodeGenerator->createBrowserTestForLineList(
            $instrumentedSource,
            $fixture,
            null,
            $teardownStatements,
            $additionalVariableIdentifiers
        );

        $testRunJob = $this->testRunner->createTestRunJob($classCode);

        if ($testRunJob instanceof TestRunJob) {
            $this->testRunner->run($testRunJob);

            $this->assertSame(
                $testRunJob->getExpectedExitCode(),
                $testRunJob->getExitCode(),
                $testRunJob->getOutputAsString()
            );
        }
    }

    public function createSourceDataProvider(): array
    {
        return [
            'browser property: size' => [
                'fixture' => '/empty.html',
                'model' => new ObjectValue(ObjectValueType::BROWSER_PROPERTY, '$browser.size', 'size'),
                'teardownStatements' => new Block([
                    StatementFactory::createAssertSame('"1200x1100"', '$value'),
                ]),
                'additionalVariableIdentifiers' => [
                    'WEBDRIVER_DIMENSION' => '$webDriverDimension',
                ],
            ],
            'page property: title' => [
                'fixture' => '/index.html',
                'model' => new ObjectValue(ObjectValueType::PAGE_PROPERTY, '$page.title', 'title'),
                'teardownStatements' => new Block([
                    StatementFactory::createAssertBrowserTitle('Test fixture web server default document'),
                ]),
            ],
            'page property: url' => [
                'fixture' => '/index.html',
                'model' => new ObjectValue(ObjectValueType::PAGE_PROPERTY, '$page.url', 'url'),
                'teardownStatements' => new Block([
                    StatementFactory::createAssertSame('"http://127.0.0.1:9080/index.html"', '$value'),
                ]),
            ],
        ];
    }
}
