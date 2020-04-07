<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Step;

use webignition\BaseBasilTestCase\Statement;
use webignition\BasilCompilableSource\Block\ClassDependencyCollection;
use webignition\BasilCompilableSource\Line\ClassDependency;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSource\VariablePlaceholderCollection;
use webignition\BasilCompilableSourceFactory\Handler\Step\StatementBlockFactory;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Assertion\DerivedElementExistsAssertion;
use webignition\BasilModels\StatementInterface as StatementModelInterface;
use webignition\BasilParser\ActionParser;
use webignition\BasilParser\AssertionParser;

class StatementBlockFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(
        StatementModelInterface $statement,
        string $expectedRenderedContent,
        MetadataInterface $expectedMetadata
    ) {
        $factory = StatementBlockFactory::createFactory();

        $codeBlock = $factory->create($statement);

        $this->assertEquals($expectedRenderedContent, $codeBlock->render());
        $this->assertEquals($expectedMetadata, $codeBlock->getMetadata());
    }

    public function createDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $assertionParser = AssertionParser::create();

        return [
            'click action' => [
                'statement' => $actionParser->parse('click $".selector"'),
                'expectedRenderedSource' =>
                    '// click $".selector"' . "\n" .
                    '{{ PHPUNIT }}->handledStatements[] = Statement::createAction(' . "\n" .
                    '    \'click $".selector"\'' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'exists assertion' => [
                'statement' => $assertionParser->parse('$".selector" exists'),
                'expectedRenderedSource' =>
                    '// $".selector" exists' . "\n" .
                    '{{ PHPUNIT }}->handledStatements[] = Statement::createAssertion(' . "\n" .
                    '    \'$".selector" exists\'' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'derived exists assertion' => [
                'statement' => new DerivedElementExistsAssertion(
                    $actionParser->parse('click $".selector"'),
                    '$".selector"'
                ),
                'expectedRenderedSource' =>
                    '// $".selector" exists <- click $".selector"' . "\n" .
                    '{{ PHPUNIT }}->handledStatements[] = Statement::createAssertion(' . "\n" .
                    '    \'$".selector" exists\',' . "\n" .
                    '    Statement::createAction(\'click $".selector"\')' . "\n" .
                    ');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                    ]),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
        ];
    }
}
