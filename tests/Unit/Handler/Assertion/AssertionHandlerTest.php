<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Assertion;

use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\AccessorDefaultValueFactory;
use webignition\BasilCompilableSourceFactory\AssertionFailureMessageFactory;
use webignition\BasilCompilableSourceFactory\AssertionMethodInvocationFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\ElementIdentifierCallFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\CreateFromExcludesAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\CreateFromExistsAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\CreateFromIncludesAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\CreateFromIsAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\CreateFromIsNotAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\CreateFromMatchesAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion\CreateFromNotExistsAssertionDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\AssertionHandler;
use webignition\BasilCompilableSourceFactory\Tests\Services\AssertionFailureMessageFactoryFactory;
use webignition\BasilCompilableSourceFactory\Tests\Services\ObjectReflector;
use webignition\BasilCompilableSourceFactory\ValueTypeIdentifier;
use webignition\BasilDomIdentifierFactory\Factory;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;
use webignition\BasilModels\Assertion\AssertionInterface;
use webignition\BasilParser\AssertionParser;
use webignition\DomElementIdentifier\ElementIdentifier;

class AssertionHandlerTest extends \PHPUnit\Framework\TestCase
{
    use CreateFromExcludesAssertionDataProviderTrait;
    use CreateFromExistsAssertionDataProviderTrait;
    use CreateFromIncludesAssertionDataProviderTrait;
    use CreateFromIsAssertionDataProviderTrait;
    use CreateFromIsNotAssertionDataProviderTrait;
    use CreateFromMatchesAssertionDataProviderTrait;
    use CreateFromNotExistsAssertionDataProviderTrait;

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
     * @dataProvider createFromExcludesAssertionDataProvider
     * @dataProvider createFromExistsAssertionDataProvider
     * @dataProvider createFromIncludesAssertionDataProvider
     * @dataProvider createFromIsAssertionDataProvider
     * @dataProvider createFromIsNotAssertionDataProvider
     * @dataProvider createFromMatchesAssertionDataProvider
     * @dataProvider createFromNotExistsAssertionDataProvider
     */
    public function testHandle(
        AssertionInterface $assertion,
        array $assertionFailureMessageFactoryCalls,
        string $expectedRenderedContent,
        MetadataInterface $expectedMetadata
    ) {
        $assertionFailureMessageFactory =
            AssertionFailureMessageFactoryFactory::create($this, $assertionFailureMessageFactoryCalls);

        $handler = $this->createAssertionHandler([
            AssertionFailureMessageFactory::class => $assertionFailureMessageFactory,
        ]);

        $source = $handler->handle($assertion);

        $this->assertEquals($expectedRenderedContent, $source->render());
        $this->assertEquals($expectedMetadata, $source->getMetadata());
    }

    /**
     * @dataProvider handleThrowsExceptionDataProvider
     */
    public function testHandleThrowsException(
        AssertionInterface $assertion,
        UnsupportedStatementException $expectedException,
        ?callable $initializer = null
    ) {
        $handler = AssertionHandler::createHandler();

        if (null !== $initializer) {
            $initializer($handler);
        }

        $this->expectExceptionObject($expectedException);

        $handler->handle($assertion);
    }

    public function handleThrowsExceptionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'unsupported comparison' => [
                'assertion' => $assertionParser->parse('$".selector" foo "value"'),
                'expectedException' => new UnsupportedStatementException(
                    $assertionParser->parse('$".selector" foo "value"')
                ),
            ],
            'existence; identifier is not supported' => [
                'assertion' => $assertionParser->parse('$elements.element_name exists'),
                'expectedException' => new UnsupportedStatementException(
                    $assertionParser->parse('$elements.element_name exists'),
                    new UnsupportedContentException(
                        UnsupportedContentException::TYPE_IDENTIFIER,
                        '$elements.element_name'
                    )
                ),
            ],
            'existence; identifier cannot be extracted' => [
                'assertion' => $assertionParser->parse('$".selector" exists'),
                'expectedException' => new  UnsupportedStatementException(
                    $assertionParser->parse('$".selector" exists'),
                    new UnsupportedContentException(
                        UnsupportedContentException::TYPE_IDENTIFIER,
                        '$".selector"'
                    )
                ),
                'initializer' => function (AssertionHandler $handler) {
                    $domIdentifierFactory = \Mockery::mock(Factory::class);
                    $domIdentifierFactory
                        ->shouldReceive('createFromIdentifierString')
                        ->with('$".selector"')
                        ->andReturnNull();

                    ObjectReflector::setProperty(
                        $handler,
                        AssertionHandler::class,
                        'domIdentifierFactory',
                        $domIdentifierFactory
                    );
                },
            ],
            'comparison; examined value is not supported' => [
                'assertion' => $assertionParser->parse('$elements.examined is "value"'),
                'expectedException' => new UnsupportedStatementException(
                    $assertionParser->parse('$elements.examined is "value"'),
                    new UnsupportedContentException(
                        UnsupportedContentException::TYPE_VALUE,
                        '$elements.examined'
                    )
                ),
            ],
            'comparison; expected value is not supported' => [
                'assertion' => $assertionParser->parse('$".selector" is $elements.expected'),
                'expectedException' => new UnsupportedStatementException(
                    $assertionParser->parse('$".selector" is $elements.expected'),
                    new UnsupportedContentException(
                        UnsupportedContentException::TYPE_VALUE,
                        '$elements.expected'
                    )
                ),
            ],
            'comparison; examined value identifier cannot be extracted' => [
                'assertion' => $assertionParser->parse('$".examined" is "value"'),
                'expectedException' => new UnsupportedStatementException(
                    $assertionParser->parse('$".examined" is "value"'),
                    new UnsupportedContentException(
                        UnsupportedContentException::TYPE_IDENTIFIER,
                        '$".examined"'
                    )
                ),
                'initializer' => function (AssertionHandler $handler) {
                    $domIdentifierFactory = \Mockery::mock(Factory::class);
                    $domIdentifierFactory
                        ->shouldReceive('createFromIdentifierString')
                        ->with('$".examined"')
                        ->andReturnNull();

                    ObjectReflector::setProperty(
                        $handler,
                        AssertionHandler::class,
                        'domIdentifierFactory',
                        $domIdentifierFactory
                    );
                },
            ],
            'comparison; expected value identifier cannot be extracted' => [
                'assertion' => $assertionParser->parse('$".examined" is $".expected"'),
                'expectedException' => new UnsupportedStatementException(
                    $assertionParser->parse('$".examined" is $".expected"'),
                    new UnsupportedContentException(
                        UnsupportedContentException::TYPE_IDENTIFIER,
                        '$".expected"'
                    )
                ),
                'initializer' => function (AssertionHandler $handler) {
                    $domIdentifierFactory = \Mockery::mock(Factory::class);

                    $domIdentifierFactory
                        ->shouldReceive('createFromIdentifierString')
                        ->with('$".examined"')
                        ->andReturn(new ElementIdentifier('.examined'));

                    $domIdentifierFactory
                        ->shouldReceive('createFromIdentifierString')
                        ->with('$".expected"')
                        ->andReturnNull();

                    ObjectReflector::setProperty(
                        $handler,
                        AssertionHandler::class,
                        'domIdentifierFactory',
                        $domIdentifierFactory
                    );
                },
            ],
        ];
    }

    /**
     * @param array<mixed> $services
     *
     * @return AssertionHandler
     */
    private function createAssertionHandler(array $services = []): AssertionHandler
    {
        $accessorDefaultValueFactory =
            $services[AccessorDefaultValueFactory::class] ?? AccessorDefaultValueFactory::createFactory();
        $assertionFailureMessageFactory =
            $services[AssertionFailureMessageFactory::class] ?? AssertionFailureMessageFactory::createFactory();
        $assertionMethodInvocationFactory =
            $services[AssertionMethodInvocationFactory::class] ?? AssertionMethodInvocationFactory::createFactory();
        $domCrawlerNavigatorCallFactory =
            $services[DomCrawlerNavigatorCallFactory::class] ?? DomCrawlerNavigatorCallFactory::createFactory();
        $domIdentifierFactory = $services[DomIdentifierFactory::class] ?? DomIdentifierFactory::createFactory();
        $domIdentifierHandler = $services[DomIdentifierHandler::class] ?? DomIdentifierHandler::createHandler();
        $identifierTypeAnalyser = $services[IdentifierTypeAnalyser::class] ?? IdentifierTypeAnalyser::create();
        $scalarValueHandler = $services[ScalarValueHandler::class] ?? ScalarValueHandler::createHandler();
        $valueTypeIdentifier = $services[ValueTypeIdentifier::class] ?? new ValueTypeIdentifier();
        $elementIdentifierCallFactory =
            $services[ElementIdentifierCallFactory::class] ?? ElementIdentifierCallFactory::createFactory();

        return new AssertionHandler(
            $accessorDefaultValueFactory,
            $assertionFailureMessageFactory,
            $assertionMethodInvocationFactory,
            $domCrawlerNavigatorCallFactory,
            $domIdentifierFactory,
            $domIdentifierHandler,
            $identifierTypeAnalyser,
            $scalarValueHandler,
            $valueTypeIdentifier,
            $elementIdentifierCallFactory
        );
    }
}
