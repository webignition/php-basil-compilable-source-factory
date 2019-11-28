<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\ModelFactory\DomIdentifier;

use webignition\BasilCompilableSourceFactory\Model\DomIdentifier;
use webignition\BasilCompilableSourceFactory\ModelFactory\DomIdentifier\IdentifierHandler;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Identifier\AttributeIdentifierDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Identifier\CssSelectorIdentifierDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Identifier\DescendantIdentifierDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Identifier\UnknownIdentifierDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Identifier\XpathExpressionIdentifierDataProviderTrait;

/**
 * @group poc208
 */
class IdentifierHandlerTest extends \PHPUnit\Framework\TestCase
{
    use AttributeIdentifierDataProviderTrait;
    use CssSelectorIdentifierDataProviderTrait;
    use XpathExpressionIdentifierDataProviderTrait;
    use DescendantIdentifierDataProviderTrait;
    use UnknownIdentifierDataProviderTrait;

    /**
     * @var IdentifierHandler
     */
    private $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = IdentifierHandler::createHandler();
    }

    /**
     * @dataProvider attributeIdentifierDataProvider
     * @dataProvider cssSelectorIdentifierDataProvider
     * @dataProvider xpathExpressionIdentifierDataProvider
     */
    public function testCreateSuccess(string $identifierString, DomIdentifier $expectedIdentifier)
    {
        $identifier = $this->handler->create($identifierString);

        $this->assertInstanceOf(DomIdentifier::class, $identifier);
        $this->assertEquals($expectedIdentifier, $identifier);
    }

    /**
     * @dataProvider descendantIdentifierDataProvider
     * @dataProvider unknownIdentifierStringDataProvider
     * @dataProvider unhandledIdentifierStringDataProvider
     */
    public function testCreateWithUnknownIdentifierString(string $identifierString)
    {
        $this->assertNull($this->handler->create($identifierString));
    }

    public function unhandledIdentifierStringDataProvider(): array
    {
        return [
            'descendant identifier' => [
                'identifierString' => '{{ $".parent" }} $".child"',
            ],
        ];
    }
}
