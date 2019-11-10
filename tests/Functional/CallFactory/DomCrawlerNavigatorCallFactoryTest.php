<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\CallFactory;

use Facebook\WebDriver\WebDriverElement;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractBrowserTestCase;
use webignition\BasilCompilableSourceFactory\Tests\Services\StatementFactory;
use webignition\BasilCompilableSourceFactory\Tests\Services\TestRunJob;
use webignition\BasilCompilationSource\Block\Block;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\MutableBlockInterface;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Identifier\DomIdentifierInterface;

class DomCrawlerNavigatorCallFactoryTest extends AbstractBrowserTestCase
{
    /**
     * @var DomCrawlerNavigatorCallFactory
     */
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = DomCrawlerNavigatorCallFactory::createFactory();
    }

    /**
     * @dataProvider createFindCallDataProvider
     */
    public function testCreateFindCall(
        string $fixture,
        DomIdentifierInterface $identifier,
        Block $teardownStatements
    ) {
        $source = $this->factory->createFindCall($identifier);

        $instrumentedSource = clone $source;

        if ($instrumentedSource instanceof MutableBlockInterface) {
            $instrumentedSource->mutateLastStatement(function ($content) {
                return '$collection = ' . $content;
            });
        }

        $classCode = $this->testCodeGenerator->createBrowserTestForBlock(
            $instrumentedSource,
            $fixture,
            null,
            $teardownStatements
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

    public function createFindCallDataProvider(): array
    {
        return [
            'no parent, has ordinal position' => [
                'fixture' => '/form.html',
                'identifier' => new DomIdentifier('input', 1),
                'teardownStatements' => new Block([
                    StatementFactory::createAssertCount('1', '$collection'),
                    new Statement('$element = $collection->get(0)'),
                    StatementFactory::createAssertInstanceOf('\'' . WebDriverElement::class . '\'', '$element'),
                    StatementFactory::createAssertSame("'input-without-value'", '$element->getAttribute(\'name\')'),
                ]),
            ],
            'has parent' => [
                'fixture' => '/form.html',
                'identifier' => (new DomIdentifier('input'))
                    ->withParentIdentifier(new DomIdentifier('form[action="/action2"]')),
                'teardownStatements' => new Block([
                    StatementFactory::createAssertCount('1', '$collection'),
                    new Statement('$element = $collection->get(0)'),
                    StatementFactory::createAssertInstanceOf('\'' . WebDriverElement::class . '\'', '$element'),
                    StatementFactory::createAssertSame("'input-2'", '$element->getAttribute(\'name\')'),
                ]),
            ],
        ];
    }
}
