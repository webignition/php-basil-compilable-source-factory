<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Step;

use webignition\BaseBasilTestCase\Statement;
use webignition\BasilCompilableSource\Block\ClassDependencyCollection;
use webignition\BasilCompilableSource\Line\ClassDependency;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Handler\Step\StatementInvocationFactory;
use webignition\BasilModels\Assertion\DerivedElementExistsAssertion;
use webignition\BasilModels\StatementInterface as StatementModelInterface;
use webignition\BasilParser\ActionParser;
use webignition\BasilParser\AssertionParser;

class StatementInvocationFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(
        StatementModelInterface $statement,
        string $expectedRenderedContent,
        MetadataInterface $expectedMetadata
    ) {
        $factory = StatementInvocationFactory::createFactory();

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
                    'Statement::createAction(' . "\n" .
                    '    \'{' . "\n" .
                    '    "source": "click $\\\\".selector\\\\"",' . "\n" .
                    '    "type": "click",' . "\n" .
                    '    "arguments": "$\\\\".selector\\\\"",' . "\n" .
                    '    "identifier": "$\\\\".selector\\\\""' . "\n" .
                    '}\'' . "\n" .
                    ')'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                    ]),
                ]),
            ],
            'exists assertion' => [
                'statement' => $assertionParser->parse('$".selector" exists'),
                'expectedRenderedSource' =>
                    'Statement::createAssertion(' . "\n" .
                    '    \'{' . "\n" .
                    '    "source": "$\\\\".selector\\\\" exists",' . "\n" .
                    '    "identifier": "$\\\\".selector\\\\"",' . "\n" .
                    '    "comparison": "exists"' . "\n" .
                    '}\'' . "\n" .
                    ')'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                    ]),
                ]),
            ],
            'derived exists assertion' => [
                'statement' => new DerivedElementExistsAssertion(
                    $actionParser->parse('click $".selector"'),
                    '$".selector"'
                ),
                'expectedRenderedSource' =>
                    'Statement::createAssertion(' . "\n" .
                    '    \'{' . "\n" .
                    '    "source": "$\\\\".selector\\\\" exists",' . "\n" .
                    '    "identifier": "$\\\\".selector\\\\"",' . "\n" .
                    '    "comparison": "exists"' . "\n" .
                    '}\',' . "\n" .
                    '    Statement::createAction(\'{' . "\n" .
                    '    "source": "click $\\\\".selector\\\\"",' . "\n" .
                    '    "type": "click",' . "\n" .
                    '    "arguments": "$\\\\".selector\\\\"",' . "\n" .
                    '    "identifier": "$\\\\".selector\\\\""' . "\n" .
                    '}\')' . "\n" .
                    ')'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                        new ClassDependency(Statement::class),
                    ]),
                ]),
            ],
        ];
    }
}
