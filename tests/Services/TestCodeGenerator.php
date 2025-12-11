<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\ClassDefinitionInterface;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\Model\VariableDependencyCollection;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\Stubble\Resolvable\Resolvable;

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
     * @param array<string, string> $additionalVariableIdentifiers
     */
    public function createBrowserTestForBlock(
        BodyInterface $body,
        string $fixture,
        ?BodyInterface $additionalSetupStatements = null,
        ?BodyInterface $teardownStatements = null,
        array $additionalVariableIdentifiers = []
    ): string {
        $codeSource = BodyFactory::createForSourceBlock($body, $teardownStatements);

        $classDefinition = ClassDefinitionFactory::createGeneratedBrowserTestForBlock(
            $fixture,
            $codeSource,
            $additionalSetupStatements
        );

        $variableDependencyIdentifiers = $this->createVariableIdentifiersForVariableDependencies(
            $classDefinition->getMetadata()->getVariableDependencies()
        );

        $resolvedClassDefinition = ResolvableRenderer::resolve($classDefinition);

        return ResolvableRenderer::resolve(new Resolvable(
            $resolvedClassDefinition,
            array_merge(
                $variableDependencyIdentifiers,
                $additionalVariableIdentifiers
            )
        ));
    }

    /**
     * @param array<string, string> $additionalVariableIdentifiers
     */
    public function createBrowserTestForClass(
        ClassDefinitionInterface $classDefinition,
        array $additionalVariableIdentifiers = []
    ): string {
        $variableDependencyIdentifiers = $this->createVariableIdentifiersForVariableDependencies(
            $classDefinition->getMetadata()->getVariableDependencies()
        );

        $resolvedClassDefinition = ResolvableRenderer::resolve($classDefinition);

        return ResolvableRenderer::resolve(new Resolvable(
            $resolvedClassDefinition,
            array_merge(
                $variableDependencyIdentifiers,
                $additionalVariableIdentifiers
            )
        ));
    }

    /**
     * @return array<string, string>
     */
    private function createVariableIdentifiersForVariableDependencies(
        VariableDependencyCollection $variableDependencies
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
            /** @var VariableDependency $variableDependency */
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
