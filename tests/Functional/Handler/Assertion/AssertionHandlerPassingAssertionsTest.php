<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\EqualityAssertionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\ExcludesAssertionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\ExistsAssertionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\IncludesAssertionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\InclusionAssertionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\IsAssertionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\IsNotAssertionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\MatchesAssertionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\NotExistsAssertionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractBrowserTestCase;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\AssertionHandler;
use webignition\BasilCompilableSourceFactory\Tests\Services\TestRunJob;
use webignition\BasilDataStructure\AssertionInterface;

class AssertionHandlerPassingAssertionsTest extends AbstractBrowserTestCase
{
    use EqualityAssertionFunctionalDataProviderTrait;
    use InclusionAssertionFunctionalDataProviderTrait;
    use ExcludesAssertionFunctionalDataProviderTrait;
    use ExistsAssertionFunctionalDataProviderTrait;
    use IncludesAssertionFunctionalDataProviderTrait;
    use IsAssertionFunctionalDataProviderTrait;
    use IsNotAssertionFunctionalDataProviderTrait;
    use MatchesAssertionFunctionalDataProviderTrait;
    use NotExistsAssertionFunctionalDataProviderTrait;

    /**
     * @var AssertionHandler
     */
    private $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = AssertionHandler::createHandler();
    }

    /**
     * @dataProvider excludesAssertionFunctionalDataProvider
     * @dataProvider existsAssertionFunctionalDataProvider
     * @dataProvider includesAssertionFunctionalDataProvider
     * @dataProvider isAssertionFunctionalDataProvider
     * @dataProvider isNotAssertionFunctionalDataProvider
     * @dataProvider matchesAssertionFunctionalDataProvider
     * @dataProvider notExistsAssertionFunctionalDataProvider
     */
    public function testCreateSource(
        string $fixture,
        AssertionInterface $assertion,
        array $additionalVariableIdentifiers = []
    ) {
        $source = $this->handler->handle($assertion);

        $classCode = $this->testCodeGenerator->createBrowserTestForBlock(
            $source,
            $fixture,
            null,
            null,
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
}
