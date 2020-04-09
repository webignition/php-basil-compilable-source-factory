<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Step;

use webignition\BaseBasilTestCase\Statement;
use webignition\BasilCompilableSource\Block\ClassDependencyCollection;
use webignition\BasilCompilableSource\Line\ClassDependency;
use webignition\BasilCompilableSource\Line\MethodInvocation\StaticObjectMethodInvocation;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSource\StaticObject;
use webignition\BasilCompilableSource\VariablePlaceholderCollection;
use webignition\BasilCompilableSourceFactory\Handler\Step\StatementBlockFactory;
use webignition\BasilCompilableSourceFactory\Handler\Step\StatementInvocationFactory;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Assertion\DerivedElementExistsAssertion;
use webignition\BasilModels\StatementInterface;
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
        StatementBlockFactory $factory,
        string $expectedRenderedContent,
        MetadataInterface $expectedMetadata
    ) {
        $codeBlock = $factory->create($statement);

        $this->assertEquals($expectedRenderedContent, $codeBlock->render());
        $this->assertEquals($expectedMetadata, $codeBlock->getMetadata());
    }

    public function createDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $assertionParser = AssertionParser::create();

        $clickAction = $actionParser->parse('click $".selector"');
        $existsAssertion = $assertionParser->parse('$".selector" exists');
        $derivedElementExistsAssertion = new DerivedElementExistsAssertion(
            $clickAction,
            '$".selector"'
        );

        return [
            'click action' => [
                'statement' => $clickAction,
                'factory' => new StatementBlockFactory(
                    $this->createMockStatementInvocationFactory(
                        $clickAction,
                        new StaticObjectMethodInvocation(
                            new StaticObject(Statement::class),
                            'createForClickAction'
                        )
                    )
                ),
                'expectedRenderedSource' =>
                    '// click $".selector"' . "\n" .
                    '{{ PHPUNIT }}->handledStatements[] = Statement::createForClickAction();'
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
                'statement' => $existsAssertion,
                'factory' => new StatementBlockFactory(
                    $this->createMockStatementInvocationFactory(
                        $existsAssertion,
                        new StaticObjectMethodInvocation(
                            new StaticObject(Statement::class),
                            'createForExistsAssertion'
                        )
                    )
                ),
                'expectedRenderedSource' =>
                    '// $".selector" exists' . "\n" .
                    '{{ PHPUNIT }}->handledStatements[] = Statement::createForExistsAssertion();'
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
                'statement' => $derivedElementExistsAssertion,
                'factory' => new StatementBlockFactory(
                    $this->createMockStatementInvocationFactory(
                        $derivedElementExistsAssertion,
                        new StaticObjectMethodInvocation(
                            new StaticObject(Statement::class),
                            'createForDerivedExistsAssertion'
                        )
                    )
                ),
                'expectedRenderedSource' =>
                    '// $".selector" exists <- click $".selector"' . "\n" .
                    '{{ PHPUNIT }}->handledStatements[] = Statement::createForDerivedExistsAssertion();'
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

    private function createMockStatementInvocationFactory(
        StatementInterface $statement,
        StaticObjectMethodInvocation $return
    ): StatementInvocationFactory {
        $statementInvocationFactory = \Mockery::mock(StatementInvocationFactory::class);

        $statementInvocationFactory
            ->shouldReceive('create')
            ->with($statement)
            ->andReturn($return);

        return $statementInvocationFactory;
    }
}
