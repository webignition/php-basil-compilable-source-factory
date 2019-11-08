<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasilCodeGenerator\BlockGenerator;
use webignition\BasilCodeGenerator\ClassGenerator;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\Block;
use webignition\BasilCompilationSource\Block\BlockInterface;
use webignition\BasilCompilationSource\ClassDefinition\ClassDefinitionInterface;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;

class TestCodeGenerator
{
    const DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME = '$this->navigator';
    const ENVIRONMENT_VARIABLE_ARRAY_VARIABLE_NAME = '$_ENV';
    const PANTHER_CLIENT_VARIABLE_NAME = 'self::$client';
    const PANTHER_CRAWLER_VARIABLE_NAME = 'self::$crawler';
    const PHPUNIT_TEST_CASE_VARIABLE_NAME = '$this';
    const WEBDRIVER_ELEMENT_INSPECTOR_VARIABLE_NAME = 'self::$inspector';
    const WEBDRIVER_ELEMENT_MUTATOR_VARIABLE_NAME = 'self::$mutator';

    private $classGenerator;
    private $codeBlockGenerator;

    public function __construct(
        ClassGenerator $classGenerator,
        BlockGenerator $blockGenerator
    ) {
        $this->classGenerator = $classGenerator;
        $this->codeBlockGenerator = $blockGenerator;
    }

    public static function create(): TestCodeGenerator
    {
        return new TestCodeGenerator(
            ClassGenerator::create(),
            BlockGenerator::create()
        );
    }

    public function createPhpUnitTestForBlock(
        BlockInterface $block,
        array $additionalVariableIdentifiers = []
    ): string {
        $blockSource = BlockFactory::createForSourceBlock($block);
        $classDefinition = ClassDefinitionFactory::createPhpUnitTestForBlock($blockSource);

        $classCode = $this->classGenerator->createForClassDefinition(
            $classDefinition,
            '\PHPUnit\Framework\TestCase',
            $additionalVariableIdentifiers
        );

        $initializerCode = $this->codeBlockGenerator->createFromBlock(Block::fromContent([
            '(new ' . $classDefinition->getName() . '())->testGeneratedCode()'
        ]));

        return $classCode . "\n\n" . $initializerCode;
    }

    public function createBrowserTestForBlock(
        BlockInterface $block,
        string $fixture,
        ?BlockInterface $additionalSetupStatements = null,
        ?BlockInterface $teardownStatements = null,
        array $additionalVariableIdentifiers = []
    ): string {
        $codeSource = BlockFactory::createForSourceBlock($block, $teardownStatements);
        $classDefinition = ClassDefinitionFactory::createGeneratedBrowserTestForBlock(
            $fixture,
            $codeSource,
            $additionalSetupStatements
        );

        $variableDependencyIdentifiers = $this->createVariableIdentifiersForVariableDependencies(
            $classDefinition->getMetadata()->getVariableDependencies()
        );

        return $this->classGenerator->createForClassDefinition(
            $classDefinition,
            'AbstractGeneratedTestCase',
            array_merge(
                $variableDependencyIdentifiers,
                $additionalVariableIdentifiers
            )
        );
    }

    public function createBrowserTestForClass(
        ClassDefinitionInterface $classDefinition,
        array $additionalVariableIdentifiers = []
    ): string {
        $variableDependencyIdentifiers = $this->createVariableIdentifiersForVariableDependencies(
            $classDefinition->getMetadata()->getVariableDependencies()
        );

        return $this->classGenerator->createForClassDefinition(
            $classDefinition,
            'AbstractGeneratedTestCase',
            array_merge(
                $variableDependencyIdentifiers,
                $additionalVariableIdentifiers
            )
        );
    }

    private function createVariableIdentifiersForVariableDependencies(
        VariablePlaceholderCollection $variableDependencies
    ): array {
        $externalVariables = [
            VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
            VariableNames::ENVIRONMENT_VARIABLE_ARRAY => self::ENVIRONMENT_VARIABLE_ARRAY_VARIABLE_NAME,
            VariableNames::PANTHER_CLIENT => self::PANTHER_CLIENT_VARIABLE_NAME,
            VariableNames::PANTHER_CRAWLER => self::PANTHER_CRAWLER_VARIABLE_NAME,
            VariableNames::PHPUNIT_TEST_CASE => self::PHPUNIT_TEST_CASE_VARIABLE_NAME,
            VariableNames::WEBDRIVER_ELEMENT_INSPECTOR => self::WEBDRIVER_ELEMENT_INSPECTOR_VARIABLE_NAME,
            VariableNames::WEBDRIVER_ELEMENT_MUTATOR => self::WEBDRIVER_ELEMENT_MUTATOR_VARIABLE_NAME,
        ];

        $variableIdentifiers = [];

        foreach ($variableDependencies as $variableDependency) {
            $name = $variableDependency->getName();
            $externalVariable = $externalVariables[$name] ?? null;

            if (null === $externalVariable) {
                throw new \RuntimeException(sprintf('Undefined dependent variable "%s"', $name));
            }

            $variableIdentifiers[$variableDependency->getName()] = $externalVariable;
        }

        return $variableIdentifiers;
    }
}
