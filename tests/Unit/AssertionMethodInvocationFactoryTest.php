<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BasilCompilableSource\Expression\LiteralExpression;
use webignition\BasilCompilableSource\Metadata\Metadata;
use webignition\BasilCompilableSource\Metadata\MetadataInterface;
use webignition\BasilCompilableSource\MethodArguments\MethodArguments;
use webignition\BasilCompilableSource\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSource\VariableDependencyCollection;
use webignition\BasilCompilableSourceFactory\AssertionMethodInvocationFactory;
use webignition\BasilCompilableSourceFactory\VariableNames;

class AssertionMethodInvocationFactoryTest extends AbstractResolvableTest
{
    private AssertionMethodInvocationFactory $assertionMethodInvocationFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertionMethodInvocationFactory = AssertionMethodInvocationFactory::createFactory();
    }

    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(
        string $assertionMethod,
        MethodArgumentsInterface $arguments,
        string $expectedRenderedInvocation,
        MetadataInterface $expectedMetadata
    ): void {
        $invocation = $this->assertionMethodInvocationFactory->create($assertionMethod, $arguments);

        $this->assertRenderResolvable($expectedRenderedInvocation, $invocation);
        $this->assertEquals($expectedMetadata, $invocation->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public function createDataProvider(): array
    {
        return [
            'no arguments, no failure message, assertTrue' => [
                'assertionMethod' => 'assertTrue',
                'arguments' => new MethodArguments(),
                'expectedRenderedInvocation' => '{{ PHPUNIT }}->assertTrue()',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'no arguments, no failure message, assertFalse' => [
                'assertionMethod' => 'assertFalse',
                'arguments' => new MethodArguments(),
                'expectedRenderedInvocation' => '{{ PHPUNIT }}->assertFalse()',
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'has arguments, no failure message, assertEquals' => [
                'assertionMethod' => 'assertEquals',
                'arguments' => new MethodArguments([
                    new LiteralExpression('100'),
                    new LiteralExpression('\'string\''),
                ]),
                'expectedRenderedInvocation' => '{{ PHPUNIT }}->assertEquals(' . "\n" .
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
                'arguments' => new MethodArguments([
                    new LiteralExpression('100'),
                    new LiteralExpression('\'string\''),
                ]),
                'expectedRenderedInvocation' => '{{ PHPUNIT }}->assertNotEquals(' . "\n" .
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
                'arguments' => new MethodArguments([
                    new LiteralExpression('100'),
                    new LiteralExpression('\'string\''),
                ]),
                'expectedRenderedInvocation' => '{{ PHPUNIT }}->assertNotEquals(' . "\n" .
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
