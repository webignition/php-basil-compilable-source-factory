<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Block\CodeBlockInterface;
use webignition\BasilCompilableSource\ClassDefinition;
use webignition\BasilCompilableSource\ClassDefinitionInterface;
use webignition\BasilCompilableSource\Line\ClassDependency;
use webignition\BasilCompilableSource\Line\EmptyLine;
use webignition\BasilCompilableSource\Line\SingleLineComment;
use webignition\BasilCompilableSource\VariablePlaceholder;
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
    private const WEBDRIVER_ASSERTION_FACTORY_VARIABLE_NAME = '$this->assertionFactory';
    private const WEBDRIVER_ACTION_FACTORY_VARIABLE_NAME = '$this->actionFactory';


    public static function create(): TestCodeGenerator
    {
        return new TestCodeGenerator();
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
        $sources = [
            new SingleLineComment('Code under test'),
            $block,
            new EmptyLine(),
            new SingleLineComment('Additional teardown statements'),
            ($teardownStatements instanceof CodeBlockInterface ? $teardownStatements : new CodeBlock()),
        ];

        $classDefinition = ClassDefinitionFactory::createGeneratedBrowserTestForBlock(
            $fixture,
            $sources,
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
            VariableNames::ASSERTION_FACTORY => self::WEBDRIVER_ASSERTION_FACTORY_VARIABLE_NAME,
            VariableNames::ACTION_FACTORY => self::WEBDRIVER_ACTION_FACTORY_VARIABLE_NAME,
        ];

        $variableIdentifiers = [];

        foreach ($variableDependencies as $variableDependency) {
            /* @var VariablePlaceholder $variableDependency */
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
