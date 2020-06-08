<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Step;

use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSource\VariableDependencyCollection;
use webignition\BasilCompilableSourceFactory\Handler\Step\StatementBlockFactory;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Action\ActionInterface;
use webignition\BasilModels\Assertion\AssertionInterface;
use webignition\BasilModels\Assertion\DerivedValueOperationAssertion;
use webignition\BasilModels\StatementInterface as StatementModelInterface;

class StatementBlockFactoryTest extends \PHPUnit\Framework\TestCase
{
    private StatementBlockFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = StatementBlockFactory::createFactory();
    }

    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(
        StatementModelInterface $statement,
        string $expectedRenderedContent,
        MetadataInterface $expectedMetadata
    ) {
        $body = $this->factory->create($statement);

        $this->assertEquals($expectedRenderedContent, $body->render());
        $this->assertEquals($expectedMetadata, $body->getMetadata());
    }

    public function createDataProvider(): array
    {
        $clickAction = \Mockery::mock(ActionInterface::class);
        $clickAction
            ->shouldReceive('getSource')
            ->andReturn('click $".selector"');
        $clickAction
            ->shouldReceive('jsonSerialize')
            ->andReturn([
                'serialised' => 'click action',
            ]);

        $existsAssertion = \Mockery::mock(AssertionInterface::class);
        $existsAssertion
            ->shouldReceive('getSource')
            ->andReturn('$".selector" exists');
        $existsAssertion
            ->shouldReceive('jsonSerialize')
            ->andReturn([
                'serialised' => 'exists assertion',
            ]);

        $derivedElementExistsAssertion = \Mockery::mock(DerivedValueOperationAssertion::class);
        $derivedElementExistsAssertion
            ->shouldReceive('getSource')
            ->andReturn('$".selector" exists');
        $derivedElementExistsAssertion
            ->shouldReceive('getSourceStatement')
            ->andReturn($clickAction);
        $derivedElementExistsAssertion
            ->shouldReceive('jsonSerialize')
            ->andReturn([
                'serialised' => 'derived exists assertion',
            ]);

        return [
            'click action' => [
                'statement' => $clickAction,
                'expectedRenderedSource' =>
                    '// click $".selector"' . "\n" .
                    '{{ PHPUNIT }}->handledStatements[] = {{ ACTION_FACTORY }}->createFromJson(\'{' . "\n" .
                    '    "serialised": "click action"' . "\n" .
                    '}\');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::ACTION_FACTORY,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'exists assertion' => [
                'statement' => $existsAssertion,
                'expectedRenderedSource' =>
                    '// $".selector" exists' . "\n" .
                    '{{ PHPUNIT }}->handledStatements[] = {{ ASSERTION_FACTORY }}->createFromJson(\'{' . "\n" .
                    '    "serialised": "exists assertion"' . "\n" .
                    '}\');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::ASSERTION_FACTORY,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'derived exists assertion' => [
                'statement' => $derivedElementExistsAssertion,
                'expectedRenderedSource' =>
                    '// $".selector" exists <- click $".selector"' . "\n" .
                    '{{ PHPUNIT }}->handledStatements[] = {{ ASSERTION_FACTORY }}->createFromJson(\'{' . "\n" .
                    '    "serialised": "derived exists assertion"' . "\n" .
                    '}\');'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::ASSERTION_FACTORY,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
        ];
    }
}
