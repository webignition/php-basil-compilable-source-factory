<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Statement;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Handler\Statement\StatementHandler;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action as ActionDataProvider;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion as AssertionDataProvider;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractResolvableTestCase;
use webignition\BasilModels\Model\Statement\Assertion\Assertion;
use webignition\BasilModels\Model\Statement\StatementInterface;
use webignition\BasilModels\Parser\ActionParser;
use webignition\BasilModels\Parser\AssertionParser;

class StatementHandlerTest extends AbstractResolvableTestCase
{
    use ActionDataProvider\CreateFromBackActionDataProviderTrait;
    use ActionDataProvider\CreateFromClickActionDataProviderTrait;
    use ActionDataProvider\CreateFromForwardActionDataProviderTrait;
    use ActionDataProvider\CreateFromReloadActionDataProviderTrait;
    use ActionDataProvider\CreateFromSetActionDataProviderTrait;
    use ActionDataProvider\CreateFromSubmitActionDataProviderTrait;
    use ActionDataProvider\CreateFromWaitActionDataProviderTrait;
    use ActionDataProvider\CreateFromWaitForActionDataProviderTrait;
    use AssertionDataProvider\CreateFromExcludesAssertionDataProviderTrait;
    use AssertionDataProvider\CreateFromIncludesAssertionDataProviderTrait;
    use AssertionDataProvider\CreateFromIsAssertionDataProviderTrait;
    use AssertionDataProvider\CreateFromIsNotAssertionDataProviderTrait;
    use AssertionDataProvider\CreateFromMatchesAssertionDataProviderTrait;
    use AssertionDataProvider\CreateFromIdentifierExistsAssertionDataProviderTrait;
    use AssertionDataProvider\CreateFromIdentifierNotExistsAssertionDataProviderTrait;
    use AssertionDataProvider\CreateFromIsRegExpAssertionDataProviderTrait;
    use AssertionDataProvider\CreateFromScalarExistsAssertionDataProviderTrait;
    use AssertionDataProvider\CreateFromScalarNotExistsAssertionDataProviderTrait;

    #[DataProvider('createFromBackActionDataProvider')]
    #[DataProvider('createFromClickActionDataProvider')]
    #[DataProvider('createFromForwardActionDataProvider')]
    #[DataProvider('createFromReloadActionDataProvider')]
    #[DataProvider('createFromSetActionDataProvider')]
    #[DataProvider('createFromSubmitActionDataProvider')]
    #[DataProvider('createFromWaitActionDataProvider')]
    #[DataProvider('createFromWaitForActionDataProvider')]
    #[DataProvider('createFromExcludesAssertionDataProvider')]
    #[DataProvider('createFromIncludesAssertionDataProvider')]
    #[DataProvider('createFromIsAssertionDataProvider')]
    #[DataProvider('createFromIsNotAssertionDataProvider')]
    #[DataProvider('createFromMatchesAssertionDataProvider')]
    #[DataProvider('createFromIdentifierExistsAssertionDataProvider')]
    #[DataProvider('createFromIdentifierNotExistsAssertionDataProvider')]
    #[DataProvider('createFromIsRegExpAssertionDataProvider')]
    #[DataProvider('createFromScalarExistsAssertionDataProvider')]
    #[DataProvider('createFromScalarNotExistsAssertionDataProvider')]
    public function testHandleSuccess(
        StatementInterface $statement,
        ?string $expectedRenderedSetup,
        string $expectedRenderedBody,
        ?MetadataInterface $expectedSetupMetadata,
        MetadataInterface $expectedBodyMetadata,
    ): void {
        $handler = StatementHandler::createHandler();
        $components = $handler->handle($statement);

        $setup = $components->getSetup();
        if (null === $setup) {
            self::assertNull($expectedRenderedSetup);
            self::assertNull($expectedSetupMetadata);
        } else {
            $this->assertRenderResolvable((string) $expectedRenderedSetup, $setup);
            $this->assertEquals($expectedSetupMetadata, $setup->getMetadata());
        }

        $this->assertRenderResolvable($expectedRenderedBody, $components->getBody());
        $this->assertEquals($expectedBodyMetadata, $components->getBody()->getMetadata());
    }

    #[DataProvider('handleThrowsExceptionDataProvider')]
    public function testHandleThrowsException(
        StatementInterface $statement,
        UnsupportedStatementException $expectedException
    ): void {
        $handler = StatementHandler::createHandler();
        $this->expectExceptionObject($expectedException);

        $handler->handle($statement);
    }

    /**
     * @return array<mixed>
     */
    public static function handleThrowsExceptionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'interaction action, identifier not dom identifier' => [
                'statement' => $actionParser->parse('click $elements.element_name', 0),
                'expectedException' => new UnsupportedStatementException(
                    $actionParser->parse('click $elements.element_name', 0),
                    new UnsupportedContentException(
                        UnsupportedContentException::TYPE_IDENTIFIER,
                        '$elements.element_name'
                    )
                ),
            ],
            'unsupported action type' => [
                'statement' => $actionParser->parse('foo $".selector"', 0),
                'expectedException' => new UnsupportedStatementException(
                    $actionParser->parse('foo $".selector"', 0)
                ),
            ],
        ];
    }

    #[DataProvider('handleThrowsUnsupportedStatementExceptionDataProvider')]
    public function testHandleThrowsUnsupportedStatementException(
        StatementInterface $statement,
        UnsupportedStatementException $expected
    ): void {
        $handler = StatementHandler::createHandler();

        try {
            $handler->handle($statement);
        } catch (UnsupportedStatementException $exception) {
            self::assertSame((string) $expected->getStatement(), (string) $exception->getStatement());
            self::assertSame($expected->getCode(), $exception->getCode());
        }
    }

    /**
     * @return array<mixed>
     */
    public static function handleThrowsUnsupportedStatementExceptionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        $invalidExistsAssertion = new Assertion(
            'invalid exists',
            0,
            'invalid',
            'exists',
        );

        return [
            'comparison assertion, unsupported comparison' => [
                'statement' => $assertionParser->parse('$".selector" foo "value"', 0),
                'expected' => new UnsupportedStatementException(
                    $assertionParser->parse('$".selector" foo "value"', 0)
                ),
            ],
            'comparison assertion, examined value is not supported' => [
                'statement' => $assertionParser->parse('$elements.examined is "value"', 0),
                'expected' => new UnsupportedStatementException(
                    $assertionParser->parse('$elements.examined is "value"', 0),
                    new UnsupportedContentException(
                        UnsupportedContentException::TYPE_VALUE,
                        '$elements.examined'
                    )
                ),
            ],
            'comparison assertion, expected value is not supported' => [
                'statement' => $assertionParser->parse('$".selector" is $elements.expected', 0),
                'expected' => new UnsupportedStatementException(
                    $assertionParser->parse('$".selector" is $elements.expected', 0),
                    new UnsupportedContentException(
                        UnsupportedContentException::TYPE_VALUE,
                        '$elements.expected'
                    )
                ),
            ],
            'existence assertion, unsupported identifier' => [
                'statement' => $invalidExistsAssertion,
                'expected' => new UnsupportedStatementException(
                    $invalidExistsAssertion,
                    new UnsupportedContentException(
                        UnsupportedContentException::TYPE_IDENTIFIER,
                        'invalid'
                    )
                ),
            ],
            'existence assertion, identifier is not supported' => [
                'statement' => $assertionParser->parse('$elements.element_name exists', 0),
                'expected' => new UnsupportedStatementException(
                    $assertionParser->parse('$elements.element_name exists', 0),
                    new UnsupportedContentException(
                        UnsupportedContentException::TYPE_IDENTIFIER,
                        '$elements.element_name'
                    )
                ),
            ],
        ];
    }
}
