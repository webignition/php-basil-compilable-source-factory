<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\ClassDefinitionInterface;
use webignition\BasilCompilationSource\LineListInterface;
use webignition\BasilCompilationSource\SourceInterface;

class TestCodeGenerator
{
    const DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME = '$this->navigator';
    const PANTHER_CLIENT_VARIABLE_NAME = 'self::$client';
    const PANTHER_CRAWLER_VARIABLE_NAME = 'self::$crawler';
    const PHPUNIT_TEST_CASE_VARIABLE_NAME = '$this';

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
        $variableIdentifiers = array_merge(
            [
                VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                VariableNames::PANTHER_CLIENT => self::PANTHER_CLIENT_VARIABLE_NAME,
                VariableNames::PANTHER_CRAWLER => self::PANTHER_CRAWLER_VARIABLE_NAME,
                VariableNames::PHPUNIT_TEST_CASE => self::PHPUNIT_TEST_CASE_VARIABLE_NAME,
            ],
            $variableIdentifiers
        );

        return $this->codeGenerator->createForClassDefinition(
            $classDefinition,
            'AbstractGeneratedTestCase',
            $variableIdentifiers
        );
    }
}
