<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\CallFactory;

use webignition\BasilCodeGenerator\CodeBlockGenerator;
use webignition\BasilCodeGenerator\LineGenerator;
use webignition\BasilCompilableSourceFactory\CallFactory\ElementLocatorCallFactory;
use webignition\BasilCompilableSourceFactory\Model\DomIdentifier;
use webignition\BasilCompilableSourceFactory\Tests\Services\TestCodeGenerator;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilCompilationSource\Block\ClassDependencyCollection;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Line\ClassDependency;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\DomElementLocator\ElementLocator;
use webignition\DomElementLocator\ElementLocatorInterface;

/**
 * @group poc208
 */
class ElementLocatorCallFactoryTest extends AbstractTestCase
{
    /**
     * @var ElementLocatorCallFactory
     */
    private $factory;

    /**
     * @var TestCodeGenerator
     */
    private $testCodeGenerator;

    /**
     * @var CodeBlockGenerator
     */
    private $codeBlockGenerator;

    /**
     * @var LineGenerator
     */
    private $lineGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = ElementLocatorCallFactory::createFactory();
        $this->testCodeGenerator = TestCodeGenerator::create();
        $this->codeBlockGenerator = CodeBlockGenerator::create();
        $this->lineGenerator = LineGenerator::create();
    }

    /**
     * @dataProvider createConstructorCallDataProvider
     */
    public function testCreateConstructorCall(
        DomIdentifier $elementIdentifier,
        ElementLocatorInterface $expectedElementLocator
    ) {
        $block = $this->factory->createConstructorCall($elementIdentifier);
        $block = new CodeBlock([
            $block,
        ]);

        $block->mutateLastStatement(function ($content) {
            return 'return ' . $content;
        });

        $expectedMetadata = (new Metadata())
            ->withClassDependencies(new ClassDependencyCollection([
                new ClassDependency(ElementLocator::class)
            ]));

        $this->assertMetadataEquals($expectedMetadata, $block->getMetadata());

        $code = $this->codeBlockGenerator->createWithUseStatementsFromBlock(new CodeBlock([
            $block,
        ]), []);

        $elementLocator = eval($code);

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
