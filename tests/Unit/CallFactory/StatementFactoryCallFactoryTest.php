<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\CallFactory;

use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSource\VariablePlaceholderCollection;
use webignition\BasilCompilableSourceFactory\CallFactory\StatementFactoryCallFactory;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Assertion\DerivedElementExistsAssertion;
use webignition\BasilModels\StatementInterface;
use webignition\BasilParser\ActionParser;
use webignition\BasilParser\AssertionParser;

class StatementFactoryCallFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var StatementFactoryCallFactory
     */
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = StatementFactoryCallFactory::createFactory();
    }

    /**
     * @dataProvider createStatementFactoryCallDataProvider
     */
    public function testCreateStatementFactoryCall(
        StatementInterface $statement,
        string $expectedRenderedContent,
        MetadataInterface $expectedMetadata
    ) {
        $objectMethodInvocation = $this->factory->create($statement);

        $this->assertEquals($expectedRenderedContent, $objectMethodInvocation->render());
        $this->assertEquals($expectedMetadata, $objectMethodInvocation->getMetadata());
    }

    public function createStatementFactoryCallDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $assertionParser = AssertionParser::create();

        return [
            'click action' => [
                'statement' => $actionParser->parse('click $".selector"'),
                'expectedRenderedSource' =>
                    '{{ ACTION_FACTORY }}->createFromJson(\'{' .  "\n" .
                    '    "source": "click $\\\\".selector\\\\"",' . "\n" .
                    '    "type": "click",' . "\n" .
                    '    "arguments": "$\\\\".selector\\\\"",' . "\n" .
                    '    "identifier": "$\\\\".selector\\\\""' . "\n" .
                    '}\')'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::ACTION_FACTORY,
                    ]),
                ]),
            ],
            'exists assertion' => [
                'statement' => $assertionParser->parse('$".selector" exists'),
                'expectedRenderedSource' =>
                    '{{ ASSERTION_FACTORY }}->createFromJson(\'{' . "\n" .
                    '    "source": "$\\\\".selector\\\\" exists",' . "\n" .
                    '    "identifier": "$\\\\".selector\\\\"",' . "\n" .
                    '    "comparison": "exists"' . "\n" .
                    '}\')'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::ASSERTION_FACTORY,
                    ]),
                ]),
            ],
            'derived exists assertion' => [
                'statement' => new DerivedElementExistsAssertion(
                    $actionParser->parse('click $".selector"'),
                    '$".selector"'
                ),
                'expectedRenderedSource' =>
                    '{{ ASSERTION_FACTORY }}->createFromJson(\'{' . "\n" .
                    '    "source_type": "action",' . "\n" .
                    '    "source": {' . "\n" .
                    '        "source": "click $\\\\".selector\\\\"",' . "\n" .
                    '        "type": "click",' . "\n" .
                    '        "arguments": "$\\\\".selector\\\\"",' . "\n" .
                    '        "identifier": "$\\\\".selector\\\\""' . "\n" .
                    '    },' . "\n" .
                    '    "identifier": "$\\\\".selector\\\\""' . "\n" .
                    '}\')'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::ASSERTION_FACTORY,
                    ]),
                ]),
            ],
        ];
    }
}
