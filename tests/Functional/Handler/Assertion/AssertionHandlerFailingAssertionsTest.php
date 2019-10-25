<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Handler\Assertion;

use PHPUnit\Framework\ExpectationFailedException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Tests\Functional\Handler\AbstractHandlerTest;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\AssertionHandler;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilModel\Assertion\AssertionInterface;
use webignition\BasilModelFactory\AssertionFactory;
use webignition\SymfonyDomCrawlerNavigator\Navigator;

class AssertionHandlerFailingAssertionsTest extends AbstractHandlerTest
{
    protected function createHandler(): HandlerInterface
    {
        return AssertionHandler::createHandler();
    }

    /**
     * @dataProvider createSourceForFailingAssertionsDataProvider
     */
    public function testCreateSourceForFailingAssertions(
        string $fixture,
        AssertionInterface $assertion,
        string $expectedExpectationFailedExceptionMessage,
        array $additionalSetupStatements = [],
        array $additionalVariableIdentifiers = [],
        ?MetadataInterface $metadata = null
    ) {
        $statementList = $this->handler->createSource($assertion);

        $variableIdentifiers = array_merge(
            [
                VariableNames::PHPUNIT_TEST_CASE => self::PHPUNIT_TEST_CASE_VARIABLE_NAME,
            ],
            $additionalVariableIdentifiers
        );

        $executableCall = $this->createExecutableCallForRequest(
            $fixture,
            $statementList,
            $additionalSetupStatements,
            [],
            $variableIdentifiers,
            $metadata
        );

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($expectedExpectationFailedExceptionMessage);

        eval($executableCall);
    }

    public function createSourceForFailingAssertionsDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'exists comparison, element identifier examined value, element does not exist' => [
                'fixture' => '/index.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector" exists'
                ),
                'expectedExpectationFailedExceptionMessage' => 'Failed asserting that false is true.',
                'additionalSetupStatements' => [
                    '$navigator = Navigator::create($crawler);',
                ],
                'additionalVariableIdentifiers' => [
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,

                ],
                'metadata' => (new Metadata())->withClassDependencies(
                    new ClassDependencyCollection([
                        new ClassDependency(Navigator::class),
                    ])
                ),
            ],
            'exists comparison, attribute identifier examined value, element does not exist' => [
                'fixture' => '/index.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector".attribute_name exists'
                ),
                'expectedExpectationFailedExceptionMessage' => 'Failed asserting that false is true.',
                'additionalSetupStatements' => [
                    '$navigator = Navigator::create($crawler);',
                ],
                'additionalVariableIdentifiers' => [
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                ],
                'metadata' => (new Metadata())->withClassDependencies(
                    new ClassDependencyCollection([
                        new ClassDependency(Navigator::class),
                    ])
                ),
            ],
            'exists comparison, attribute identifier examined value, attribute does not exist' => [
                'fixture' => '/index.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '"h1".attribute_name exists'
                ),
                'expectedExpectationFailedExceptionMessage' => 'Failed asserting that false is true.',
                'additionalSetupStatements' => [
                    '$navigator = Navigator::create($crawler);',
                ],
                'additionalVariableIdentifiers' => [
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                ],
                'metadata' => (new Metadata())->withClassDependencies(
                    new ClassDependencyCollection([
                        new ClassDependency(Navigator::class),
                    ])
                ),
            ],
            'exists comparison, environment examined value, environment variable does not exist' => [
                'fixture' => '/index.html',
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$env.FOO exists'
                ),
                'expectedExpectationFailedExceptionMessage' => 'Failed asserting that false is true.',
                'additionalSetupStatements' => [],
                'additionalVariableIdentifiers' => [
                    VariableNames::ENVIRONMENT_VARIABLE_ARRAY => '$_ENV',
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                ],
            ],
        ];
    }
}
