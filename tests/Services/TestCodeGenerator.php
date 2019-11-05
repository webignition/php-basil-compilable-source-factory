<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\ClassDefinitionInterface;
use webignition\BasilCompilationSource\LineListInterface;
use webignition\BasilCompilationSource\SourceInterface;
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

    private $codeGenerator;

    public function __construct(CodeGenerator $codeGenerator)
    {
        $this->codeGenerator = $codeGenerator;
    }

    public static function create(): TestCodeGenerator
    {
        return new TestCodeGenerator(CodeGenerator::create());
    }

    public function createForLineList(
        SourceInterface $source,
        string $fixture,
        ?LineListInterface $additionalSetupStatements = null,
        ?LineListInterface $teardownStatements = null,
        array $additionalVariableIdentifiers = []
    ): string {
        $codeSource = LineListFactory::createForSourceLineList($source, $teardownStatements);
        $classDefinition = ClassDefinitionFactory::createForLineList($fixture, $codeSource, $additionalSetupStatements);

        return $this->createForClassDefinition(
            $classDefinition,
            $additionalVariableIdentifiers
        );
    }

    public function createForClassDefinition(
        ClassDefinitionInterface $classDefinition,
        array $variableIdentifiers
    ): string {
        $variableDependencyIdentifiers = $this->createVariableIdentifiersForVariableDependencies(
            $classDefinition->getMetadata()->getVariableDependencies()
        );

        return $this->codeGenerator->createForClassDefinition(
            $classDefinition,
            'AbstractGeneratedTestCase',
            array_merge(
                $variableDependencyIdentifiers,
                $variableIdentifiers
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
            $id = $variableDependency->getId();
            $externalVariable = $externalVariables[$id] ?? null;

            if (null === $externalVariable) {
                throw new \RuntimeException(sprintf('Undefined dependent variable "%s"', $id));
            }

            $variableIdentifiers[$variableDependency->getId()] = $externalVariable;
        }

        return $variableIdentifiers;
    }
}
