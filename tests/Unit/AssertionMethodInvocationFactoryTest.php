<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BasilCompilableSource\Line\ExpressionInterface;
use webignition\BasilCompilableSource\Line\LiteralExpression;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSource\VariablePlaceholderCollection;
use webignition\BasilCompilableSourceFactory\AssertionMethodInvocationFactory;
use webignition\BasilCompilableSourceFactory\VariableNames;

class AssertionMethodInvocationFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AssertionMethodInvocationFactory
     */
    private $assertionMethodInvocationFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertionMethodInvocationFactory = AssertionMethodInvocationFactory::createFactory();
    }

    /**
     * @dataProvider createDataProvider
     *
     * @param string $assertionMethod
     * @param array<string, ExpressionInterface> $arguments
     * @param string $failureMessage
     * @param string $expectedRenderedInvocation
     * @param MetadataInterface $expectedMetadata
     */
    public function testCreate(
        string $assertionMethod,
        array $arguments,
        string $failureMessage,
        string $expectedRenderedInvocation,
        MetadataInterface $expectedMetadata
    ) {
        $invocation = $this->assertionMethodInvocationFactory->create($assertionMethod, $arguments, $failureMessage);

        $this->assertSame($invocation->render(), $expectedRenderedInvocation);
        $this->assertEquals($expectedMetadata, $invocation->getMetadata());
    }

    public function createDataProvider(): array
    {
        return [
            'no arguments, no failure message, assertTrue' => [
                'assertionMethod' => 'assertTrue',
                'arguments' => [],
                'failureMessage' => '',
                'expectedRenderedInvocation' => '{{ PHPUNIT }}->assertTrue()',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'no arguments, no failure message, assertFalse' => [
                'assertionMethod' => 'assertFalse',
                'arguments' => [],
                'failureMessage' => '',
                'expectedRenderedInvocation' => '{{ PHPUNIT }}->assertFalse()',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'has arguments, no failure message, assertEquals' => [
                'assertionMethod' => 'assertEquals',
                'arguments' => [
                    new LiteralExpression('100'),
                    new LiteralExpression('\'string\''),
                ],
                'failureMessage' => '',
                'expectedRenderedInvocation' =>
                    '{{ PHPUNIT }}->assertEquals(' . "\n" .
                    '    100,' . "\n" .
                    '    \'string\'' . "\n" .
                    ')'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'has arguments, has failure message, assertNotEquals' => [
                'assertionMethod' => 'assertNotEquals',
                'arguments' => [
                    new LiteralExpression('100'),
                    new LiteralExpression('\'string\''),
                ],
                'failureMessage' => 'failure message content',
                'expectedRenderedInvocation' =>
                    '{{ PHPUNIT }}->assertNotEquals(' . "\n" .
                    '    100,' . "\n" .
                    '    \'string\',' . "\n" .
                    '    \'failure message content\'' . "\n" .
                    ')'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'has arguments, has failure message containing quotes, assertNotEquals' => [
                'assertionMethod' => 'assertNotEquals',
                'arguments' => [
                    new LiteralExpression('100'),
                    new LiteralExpression('\'string\''),
                ],
                'failureMessage' => 'failure \'message\' content',
                'expectedRenderedInvocation' =>
                    '{{ PHPUNIT }}->assertNotEquals(' . "\n" .
                    '    100,' . "\n" .
                    '    \'string\',' . "\n" .
                    '    \'failure \\\'message\\\' content\'' . "\n" .
                    ')'
                ,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => VariablePlaceholderCollection::createDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
        ];
    }
}
