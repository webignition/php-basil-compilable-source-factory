<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\CallFactory;

use webignition\BasilCodeGenerator\CodeBlockGenerator;
use webignition\BasilCodeGenerator\LineGenerator;
use webignition\BasilCompilableSource\Block\ClassDependencyCollection;
use webignition\BasilCompilableSource\Line\ClassDependency;
use webignition\BasilCompilableSource\Line\Statement\ReturnStatement;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\CallFactory\ElementIdentifierCallFactory;
use webignition\BasilCompilableSourceFactory\Tests\Services\TestCodeGenerator;
use webignition\DomElementIdentifier\ElementIdentifier;
use webignition\DomElementIdentifier\ElementIdentifierInterface;

class ElementIdentifierCallFactoryTest extends \PHPUnit\Framework\TestCase
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
        $constructorExpression = $this->factory->createConstructorCall($elementIdentifier);

        $this->assertEquals(
            new Metadata([
                Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                    new ClassDependency(ElementIdentifier::class),
                ])
            ]),
            $constructorExpression->getMetadata()
        );

        $constructorAccessStatement = new ReturnStatement($constructorExpression);

        $code = $constructorAccessStatement->getMetadata()->getClassDependencies()->render() .
            "\n" .
            $constructorAccessStatement->render();

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
