<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Transpiler\Assertion;

use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\ExcludesAssertionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\ExistsAssertionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\Functional\Transpiler\AbstractTranspilerTest;
use webignition\BasilCompilableSourceFactory\Transpiler\Assertion\AssertionTranspiler;
use webignition\BasilCompilableSourceFactory\Transpiler\TranspilerInterface;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilModel\Assertion\AssertionInterface;

class AssertionTranspilerPassingAssertionsTest extends AbstractTranspilerTest
{
    use ExcludesAssertionFunctionalDataProviderTrait;
    use ExistsAssertionFunctionalDataProviderTrait;

    protected function createTranspiler(): TranspilerInterface
    {
        return AssertionTranspiler::createTranspiler();
    }

    /**
     * @dataProvider excludesAssertionFunctionalDataProvider
     * @dataProvider existsAssertionFunctionalDataProvider
     */
    public function testTranspile(
        string $fixture,
        AssertionInterface $model,
        array $additionalSetupStatements = [],
        array $additionalVariableIdentifiers = [],
        ?MetadataInterface $metadata = null
    ) {
        $source = $this->transpiler->transpile($model);

        $variableIdentifiers = array_merge(
            [
                VariableNames::PHPUNIT_TEST_CASE => self::PHPUNIT_TEST_CASE_VARIABLE_NAME,
            ],
            $additionalVariableIdentifiers
        );

        $executableCall = $this->createExecutableCallForRequest(
            $fixture,
            $source,
            $additionalSetupStatements,
            $variableIdentifiers,
            $metadata
        );

        echo $executableCall . "\n\n";

        eval($executableCall);
    }
}
