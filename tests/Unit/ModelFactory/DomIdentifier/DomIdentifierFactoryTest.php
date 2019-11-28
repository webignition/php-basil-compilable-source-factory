<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\ModelFactory\DomIdentifier;

use webignition\BasilCompilableSourceFactory\Exception\UnknownIdentifierException;
use webignition\BasilCompilableSourceFactory\Model\DomIdentifier;
use webignition\BasilCompilableSourceFactory\ModelFactory\DomIdentifier\DomIdentifierFactory;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Identifier\AttributeIdentifierDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Identifier\CssSelectorIdentifierDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Identifier\DescendantIdentifierDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Identifier\UnknownIdentifierDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Identifier\XpathExpressionIdentifierDataProviderTrait;

/**
 * @group poc208
 */
class DomIdentifierFactoryTest extends \PHPUnit\Framework\TestCase
{
    use AttributeIdentifierDataProviderTrait;
    use CssSelectorIdentifierDataProviderTrait;
    use XpathExpressionIdentifierDataProviderTrait;
    use DescendantIdentifierDataProviderTrait;
    use UnknownIdentifierDataProviderTrait;

    /**
     * @var DomIdentifierFactory
     */
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = DomIdentifierFactory::createFactory();
    }

    /**
     * @dataProvider attributeIdentifierDataProvider
     * @dataProvider cssSelectorIdentifierDataProvider
     * @dataProvider xpathExpressionIdentifierDataProvider
     * @dataProvider descendantIdentifierDataProvider
     */
    public function testCreateSuccess(string $identifierString, DomIdentifier $expectedIdentifier)
    {
        $identifier = $this->factory->create($identifierString);

        $this->assertInstanceOf(DomIdentifier::class, $identifier);
        $this->assertEquals($expectedIdentifier, $identifier);
    }

    /**
     * @dataProvider unknownIdentifierStringDataProvider
     */
    public function testCreateWithUnknownIdentifierString(string $identifierString)
    {
        $this->expectExceptionObject(new UnknownIdentifierException($identifierString));

        $this->factory->create($identifierString);
    }

    public function testCreateUnknownIdentifier()
    {
        $identifierString = 'foo';
        $this->expectExceptionObject(new UnknownIdentifierException($identifierString));

        $this->factory->create($identifierString);
    }
}
