<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Step;

use webignition\BasilCompilableSourceFactory\Handler\Step\StatementBlockFactory;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Tests\Unit\AbstractResolvableTestCase;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Model\Action\ActionInterface;
use webignition\BasilModels\Model\Assertion\AssertionInterface;
use webignition\BasilModels\Model\Assertion\DerivedValueOperationAssertion;
use webignition\BasilModels\Model\StatementInterface as StatementModelInterface;

class StatementBlockFactoryTest extends AbstractResolvableTestCase
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
    ): void {
        $body = $this->factory->create($statement);

        $this->assertRenderResolvable($expectedRenderedContent, $body);
        $this->assertEquals($expectedMetadata, $body->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public static function createDataProvider(): array
    {
        $clickAction = \Mockery::mock(ActionInterface::class);
        $clickAction
            ->shouldReceive('getSource')
            ->andReturn('click $".selector"')
        ;
        $clickAction
            ->shouldReceive('jsonSerialize')
            ->andReturn([
                'serialised' => 'click action',
            ])
        ;

        $existsAssertion = \Mockery::mock(AssertionInterface::class);
        $existsAssertion
            ->shouldReceive('getSource')
            ->andReturn('$".selector" exists')
        ;
        $existsAssertion
            ->shouldReceive('jsonSerialize')
            ->andReturn([
                'serialised' => 'exists assertion',
            ])
        ;

        $derivedElementExistsAssertion = \Mockery::mock(DerivedValueOperationAssertion::class);
        $derivedElementExistsAssertion
            ->shouldReceive('getSource')
            ->andReturn('$".selector" exists')
        ;
        $derivedElementExistsAssertion
            ->shouldReceive('getSourceStatement')
            ->andReturn($clickAction)
        ;
        $derivedElementExistsAssertion
            ->shouldReceive('jsonSerialize')
            ->andReturn([
                'serialised' => 'derived exists assertion',
            ])
        ;

        return [
            'click action' => [
                'statement' => $clickAction,
                'expectedRenderedContent' => '// click $".selector"' . "\n"
                    . '{{ PHPUNIT }}->handledStatements[] = {{ ACTION_FACTORY }}->createFromJson(\'{' . "\n"
                    . '    "serialised": "click action"' . "\n"
                    . '}\');',
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableNames::ACTION_FACTORY,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'exists assertion' => [
                'statement' => $existsAssertion,
                'expectedRenderedContent' => '// $".selector" exists' . "\n"
                    . '{{ PHPUNIT }}->handledStatements[] = {{ ASSERTION_FACTORY }}->createFromJson(\'{' . "\n"
                    . '    "serialised": "exists assertion"' . "\n"
                    . '}\');',
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableNames::ASSERTION_FACTORY,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'derived exists assertion' => [
                'statement' => $derivedElementExistsAssertion,
                'expectedRenderedContent' => '// $".selector" exists <- click $".selector"' . "\n"
                    . '{{ PHPUNIT }}->handledStatements[] = {{ ASSERTION_FACTORY }}->createFromJson(\'{' . "\n"
                    . '    "serialised": "derived exists assertion"' . "\n"
                    . '}\');',
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableNames::ASSERTION_FACTORY,
                        VariableNames::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
        ];
    }
}
