<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use PHPUnit\Framework\TestCase;
use webignition\BasilCodeGenerator\ClassGenerator;
use webignition\BasilCodeGenerator\UnresolvedPlaceholderException;
use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Block\CodeBlockInterface;
use webignition\BasilCompilableSource\ClassDefinition;
use webignition\BasilCompilableSource\ClassDefinitionInterface;
use webignition\BasilCompilableSource\Line\ClassDependency;
use webignition\BasilCompilableSource\Line\LiteralExpression;
use webignition\BasilCompilableSource\Line\Statement\Statement;
use webignition\BasilCompilableSource\VariablePlaceholderCollection;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractGeneratedTestCase;
use webignition\BasilCompilableSourceFactory\VariableNames;

class TestCodeGenerator
{
    private const DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME = '$this->navigator';
    private const ENVIRONMENT_VARIABLE_ARRAY_VARIABLE_NAME = '$_ENV';
    private const PANTHER_CLIENT_VARIABLE_NAME = 'self::$client';
    private const PANTHER_CRAWLER_VARIABLE_NAME = 'self::$crawler';
    private const PHPUNIT_TEST_CASE_VARIABLE_NAME = '$this';
    private const WEBDRIVER_ELEMENT_INSPECTOR_VARIABLE_NAME = 'self::$inspector';
    private const WEBDRIVER_ELEMENT_MUTATOR_VARIABLE_NAME = 'self::$mutator';

    private $classGenerator;

    public function __construct(ClassGenerator $classGenerator)
    {
        $this->classGenerator = $classGenerator;
    }

    public static function create(): TestCodeGenerator
    {
        return new TestCodeGenerator(
            ClassGenerator::create()
        );
    }

    /**
     * @param CodeBlockInterface $block
     * @param array<string, string> $additionalVariableIdentifiers
     *
     * @return string
     */
    public function createPhpUnitTestForBlock(
        CodeBlockInterface $block,
        array $additionalVariableIdentifiers = []
    ): string {
        $blockSource = CodeBlockFactory::createForSourceBlock($block);
        $classDefinition = ClassDefinitionFactory::createPhpUnitTestForBlock($blockSource);

        if ($classDefinition instanceof ClassDefinition) {
            $classDefinition->setBaseClass(new ClassDependency(TestCase::class));
        }

        $initializer = new CodeBlock([
            new Statement(
                new LiteralExpression('(new ' . $classDefinition->getName() . '())->testGeneratedCode()')
            )
        ]);

        $classCode = VariablePlaceholderResolver::resolve($classDefinition->render(), $additionalVariableIdentifiers);

        return $classCode . "\n\n" . $initializer->render();
    }

    /**
     * @param CodeBlockInterface $block
     * @param string $fixture
     * @param CodeBlockInterface|null $additionalSetupStatements
     * @param CodeBlockInterface|null $teardownStatements
     * @param array<string, string> $additionalVariableIdentifiers
     *
     * @return string
     */
    public function createBrowserTestForBlock(
        CodeBlockInterface $block,
        string $fixture,
        ?CodeBlockInterface $additionalSetupStatements = null,
        ?CodeBlockInterface $teardownStatements = null,
        array $additionalVariableIdentifiers = []
    ): string {
        $codeSource = CodeBlockFactory::createForSourceBlock($block, $teardownStatements);

        $classDefinition = ClassDefinitionFactory::createGeneratedBrowserTestForBlock(
            $fixture,
            $codeSource,
            $additionalSetupStatements
        );

        $variableDependencyIdentifiers = $this->createVariableIdentifiersForVariableDependencies(
            $classDefinition->getMetadata()->getVariableDependencies()
        );

        return VariablePlaceholderResolver::resolve(
            $classDefinition->render(),
            array_merge(
                $variableDependencyIdentifiers,
                $additionalVariableIdentifiers
            )
        );
    }

    /**
     * @param ClassDefinitionInterface $classDefinition
     * @param array<string, string> $additionalVariableIdentifiers
     *
     * @return string
     *
     * @throws UnresolvedPlaceholderException
     */
    public function createBrowserTestForClass(
        ClassDefinitionInterface $classDefinition,
        array $additionalVariableIdentifiers = []
    ): string {
        $variableDependencyIdentifiers = $this->createVariableIdentifiersForVariableDependencies(
            $classDefinition->getMetadata()->getVariableDependencies()
        );

        if ($classDefinition instanceof ClassDefinition) {
            $classDefinition->setBaseClass(
                new ClassDependency(AbstractGeneratedTestCase::class)
            );
        }

        return VariablePlaceholderResolver::resolve(
            $classDefinition->render(),
            array_merge(
                $variableDependencyIdentifiers,
                $additionalVariableIdentifiers
            )
        );

//        return $classDefinition->render();
//
//        return $this->classGenerator->createForClassDefinition(
//            $classDefinition,
//            'AbstractGeneratedTestCase',
//            array_merge(
//                $variableDependencyIdentifiers,
//                $additionalVariableIdentifiers
//            )
//        );
    }

    /**
     * @param VariablePlaceholderCollection $variableDependencies
     *
     * @return array<string, string>
     */
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
