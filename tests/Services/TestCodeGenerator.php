<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\ClassDefinitionInterface;
use webignition\BasilCompilableSourceFactory\Model\VariableDependencyCollection;
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
    private const MESSAGE_FACTORY_VARIABLE_NAME = 'self::$messageFactory';

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
            DependencyName::DOM_CRAWLER_NAVIGATOR->value => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
            DependencyName::ENVIRONMENT_VARIABLE_ARRAY->value => self::ENVIRONMENT_VARIABLE_ARRAY_VARIABLE_NAME,
            DependencyName::PANTHER_CLIENT->value => self::PANTHER_CLIENT_VARIABLE_NAME,
            DependencyName::PANTHER_CRAWLER->value => self::PANTHER_CRAWLER_VARIABLE_NAME,
            DependencyName::PHPUNIT_TEST_CASE->value => self::PHPUNIT_TEST_CASE_VARIABLE_NAME,
            DependencyName::WEBDRIVER_ELEMENT_INSPECTOR->value => self::WEBDRIVER_ELEMENT_INSPECTOR_VARIABLE_NAME,
            DependencyName::WEBDRIVER_ELEMENT_MUTATOR->value => self::WEBDRIVER_ELEMENT_MUTATOR_VARIABLE_NAME,
            DependencyName::MESSAGE_FACTORY->value => self::MESSAGE_FACTORY_VARIABLE_NAME,
        ];

        $variableIdentifiers = [];

        foreach ($variableDependencies as $variableDependency) {
            $name = $variableDependency->getName();
            $variableIdentifiers[$variableDependency->getName()] = $externalVariables[$name];
        }

        return $variableIdentifiers;
    }
}
