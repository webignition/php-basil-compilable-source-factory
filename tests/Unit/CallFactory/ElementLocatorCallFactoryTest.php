<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\CallFactory;

use webignition\BasilCompilableSourceFactory\CallFactory\ElementLocatorCallFactory;
use webignition\BasilCompilableSourceFactory\Tests\Services\ExecutableCallFactory;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\StatementList;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Identifier\DomIdentifierInterface;
use webignition\DomElementLocator\ElementLocator;
use webignition\DomElementLocator\ElementLocatorInterface;

class ElementLocatorCallFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ElementLocatorCallFactory
     */
    private $factory;

    /**
     * @var ExecutableCallFactory
     */
    private $executableCallFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = ElementLocatorCallFactory::createFactory();
        $this->executableCallFactory = ExecutableCallFactory::createFactory();
    }

    /**
     * @dataProvider createConstructorCallDataProvider
     */
    public function testCreateConstructorCall(
        DomIdentifierInterface $elementIdentifier,
        ElementLocatorInterface $expectedElementLocator
    ) {
        $statement = $this->factory->createConstructorCall($elementIdentifier);

        $expectedMetadata = (new Metadata())
            ->withClassDependencies(new ClassDependencyCollection([
                new ClassDependency(ElementLocator::class)
            ]));

        $this->assertEquals($expectedMetadata, $statement->getMetadata());

        $executableCall = $this->executableCallFactory->createWithReturn(new StatementList([
            $statement
        ]));

        $elementLocator = eval($executableCall);

        $this->assertEquals($expectedElementLocator, $elementLocator);
    }

    public function createConstructorCallDataProvider(): array
    {
        $elementLocator = '.selector';

        return [
            'css selector, no quotes in selector, position default' => [
                'elementIdentifier' => new DomIdentifier($elementLocator),
                'expectedElementLocator' => new ElementLocator('.selector'),
            ],
            'css selector, no quotes in selector, position 1' => [
                'elementIdentifier' => new DomIdentifier($elementLocator, 1),
                'expectedElementLocator' => new ElementLocator('.selector', 1),
            ],
            'css selector, no quotes in selector, position 2' => [
                'elementIdentifier' => new DomIdentifier($elementLocator, 2),
                'expectedElementLocator' => new ElementLocator('.selector', 2),
            ],
            'css selector, double quotes in selector, position default' => [
                'elementIdentifier' => new DomIdentifier('input[name="email"]'),
                'expectedElementLocator' => new ElementLocator('input[name="email"]'),
            ],
            'css selector, single quotes in selector, position default' => [
                'elementIdentifier' => new DomIdentifier("input[name='email']"),
                'expectedElementLocator' => new ElementLocator("input[name='email']"),
            ],
            'css selector, escaped single quotes in selector, position default' => [
                'elementIdentifier' => new DomIdentifier("input[value='\'quoted\'']"),
                'expectedElementLocator' => new ElementLocator("input[value='\'quoted\'']"),
            ],
        ];
    }
}
