<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\CallFactory;

use webignition\BasilCodeGenerator\CodeBlockGenerator;
use webignition\BasilCodeGenerator\LineGenerator;
use webignition\BasilCompilableSourceFactory\CallFactory\ElementIdentifierCallFactory;
use webignition\BasilCompilableSourceFactory\Tests\Services\TestCodeGenerator;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractTestCase;
use webignition\BasilCompilationSource\Block\ClassDependencyCollection;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Line\ClassDependency;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\DomElementIdentifier\ElementIdentifier;
use webignition\DomElementIdentifier\ElementIdentifierInterface;

class ElementIdentifierCallFactoryTest extends AbstractTestCase
{
    /**
     * @var ElementIdentifierCallFactory
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

        $this->factory = ElementIdentifierCallFactory::createFactory();
        $this->testCodeGenerator = TestCodeGenerator::create();
        $this->codeBlockGenerator = CodeBlockGenerator::create();
        $this->lineGenerator = LineGenerator::create();
    }

    /**
     * @dataProvider createConstructorCallDataProvider
     */
    public function testCreateConstructorCall(ElementIdentifierInterface $elementIdentifier)
    {
        $this->markTestSkipped();

        $block = $this->factory->createConstructorCall($elementIdentifier);
        $block = new CodeBlock([
            $block,
        ]);

        $block->mutateLastStatement(function ($content) {
            return 'return ' . $content;
        });

        $expectedMetadata = (new Metadata())
            ->withClassDependencies(new ClassDependencyCollection([
                new ClassDependency(ElementIdentifier::class)
            ]));

        $this->assertMetadataEquals($expectedMetadata, $block->getMetadata());

        $code = $this->codeBlockGenerator->createWithUseStatementsFromBlock(new CodeBlock([
            $block,
        ]), []);

        $evaluatedCodeOutput = eval($code);

        $this->assertEquals($elementIdentifier, $evaluatedCodeOutput);
    }

    public function createConstructorCallDataProvider(): array
    {
        return [
            'css selector, no quotes in selector, position default' => [
                'elementIdentifier' => new ElementIdentifier('.selector'),
            ],
            'css selector, no quotes in selector, position 1' => [
                'elementIdentifier' => new ElementIdentifier('.selector', 1),
            ],
            'css selector, no quotes in selector, position 2' => [
                'elementIdentifier' => new ElementIdentifier('.selector', 2),
            ],
            'css selector, double quotes in selector, position default' => [
                'elementIdentifier' => new ElementIdentifier('input[name="email"]'),
            ],
            'css selector, single quotes in selector, position default' => [
                'elementIdentifier' => new ElementIdentifier("input[name='email']"),
            ],
            'css selector, escaped single quotes in selector, position default' => [
                'elementIdentifier' => new ElementIdentifier("input[value='\'quoted\'']"),
            ],
            'css selector, escaped single quotes within selector' => [
                'elementIdentifier' => new ElementIdentifier("input[value='va\'l\'ue']"),
            ],
            'css selector, escaped double quotes in selector, position default' => [
                'elementIdentifier' => new ElementIdentifier("input[value=\"'quoted'\"]"),
            ],
        ];
    }
}
