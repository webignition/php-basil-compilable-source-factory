<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BasilCompilableSource\Expression\ExpressionInterface;
use webignition\BasilCompilableSource\Expression\LiteralExpression;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSource\VariableDependencyCollection;
use webignition\BasilCompilableSourceFactory\AssertionMethodInvocationFactory;
use webignition\BasilCompilableSourceFactory\VariableNames;

class AssertionMethodInvocationFactoryTest extends \PHPUnit\Framework\TestCase
{
    private AssertionMethodInvocationFactory $assertionMethodInvocationFactory;

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
     * @param string $expectedRenderedInvocation
     * @param MetadataInterface $expectedMetadata
     */
    public function testCreate(
        string $assertionMethod,
        array $arguments,
        string $expectedRenderedInvocation,
        MetadataInterface $expectedMetadata
    ) {
        $invocation = $this->assertionMethodInvocationFactory->create($assertionMethod, $arguments);

        $this->assertSame($invocation->render(), $expectedRenderedInvocation);
        $this->assertEquals($expectedMetadata, $invocation->getMetadata());
    }

    public function createDataProvider(): array
    {
        return [
            'no arguments, no failure message, assertTrue' => [
                'assertionMethod' => 'assertTrue',
                'arguments' => [],
                'expectedRenderedInvocation' => '{{ PHPUNIT }}->assertTrue()',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'no arguments, no failure message, assertFalse' => [
                'assertionMethod' => 'assertFalse',
                'arguments' => [],
                'expectedRenderedInvocation' => '{{ PHPUNIT }}->assertFalse()',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
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
                'expectedRenderedInvocation' =>
                    '{{ PHPUNIT }}->assertEquals(' . "\n" .
                    '    100,' . "\n" .
                    '    \'string\'' . "\n" .
                    ')',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
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
                'expectedRenderedInvocation' =>
                    '{{ PHPUNIT }}->assertNotEquals(' . "\n" .
                    '    100,' . "\n" .
                    '    \'string\'' . "\n" .
                    ')',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
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
                'expectedRenderedInvocation' =>
                    '{{ PHPUNIT }}->assertNotEquals(' . "\n" .
                    '    100,' . "\n" .
                    '    \'string\'' . "\n" .
                    ')',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
        ];
    }
}
