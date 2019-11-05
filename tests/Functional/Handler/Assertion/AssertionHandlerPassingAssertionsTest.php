<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\EqualityAssertionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\ExcludesAssertionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\ExistsAssertionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\IncludesAssertionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\InclusionAssertionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\IsAssertionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\IsNotAssertionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\MatchesAssertionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\NotExistsAssertionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\Functional\Handler\AbstractHandlerTest;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\AssertionHandler;
use webignition\BasilModel\Assertion\AssertionInterface;

class AssertionHandlerPassingAssertionsTest extends AbstractHandlerTest
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

    protected function createHandler(): HandlerInterface
    {
        return AssertionHandler::createHandler();
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
        AssertionInterface $model,
        array $additionalVariableIdentifiers = []
    ) {
        $source = $this->handler->createSource($model);

        $classCode = $this->testCodeGenerator->createForLineList(
            $source,
            $fixture,
            null,
            null,
            $additionalVariableIdentifiers
        );

        $testRunJob = $this->testRunner->createTestRunJob($classCode);
        $this->testRunner->run($testRunJob);

        $this->assertSame(
            $testRunJob->getExpectedExitCode(),
            $testRunJob->getExitCode(),
            $testRunJob->getOutputAsString()
        );
    }
}
