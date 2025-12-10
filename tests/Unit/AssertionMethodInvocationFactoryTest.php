<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BasilCompilableSourceFactory\AssertionMethodInvocationFactory;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSourceFactory\Model\VariableDependencyCollection;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilModels\Model\Assertion\AssertionInterface;

class AssertionMethodInvocationFactoryTest extends AbstractResolvableTestCase
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
        $assertion = \Mockery::mock(AssertionInterface::class);

        $stepName = md5((string) rand());
        $metadata = new \webignition\BasilCompilableSourceFactory\Metadata\Metadata($stepName, $assertion);

        $invocation = $this->assertionMethodInvocationFactory->create($assertionMethod, $metadata, $arguments);

        $this->assertRenderResolvable($expectedRenderedInvocation, $invocation);
        $this->assertEquals($expectedMetadata, $invocation->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public static function createDataProvider(): array
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
//            'no arguments, no failure message, assertFalse' => [
//                'assertionMethod' => 'assertFalse',
//                'arguments' => new MethodArguments(),
//                'expectedRenderedInvocation' => '{{ PHPUNIT }}->assertFalse()',
//                'expectedMetadata' => new Metadata([
//                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
//                        VariableNames::PHPUNIT_TEST_CASE,
//                    ]),
//                ]),
//            ],
//            'has arguments, no failure message, assertEquals' => [
//                'assertionMethod' => 'assertEquals',
//                'arguments' => new MethodArguments([
//                    new LiteralExpression('100'),
//                    new LiteralExpression('\'string\''),
//                ]),
//                'expectedRenderedInvocation' => '{{ PHPUNIT }}->assertEquals(' . "\n"
//                    . '    100,' . "\n"
//                    . '    \'string\'' . "\n"
//                    . ')',
//                'expectedMetadata' => new Metadata([
//                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
//                        VariableNames::PHPUNIT_TEST_CASE,
//                    ]),
//                ]),
//            ],
//            'has arguments, has failure message, assertNotEquals' => [
//                'assertionMethod' => 'assertNotEquals',
//                'arguments' => new MethodArguments([
//                    new LiteralExpression('100'),
//                    new LiteralExpression('\'string\''),
//                ]),
//                'expectedRenderedInvocation' => '{{ PHPUNIT }}->assertNotEquals(' . "\n"
//                    . '    100,' . "\n"
//                    . '    \'string\'' . "\n"
//                    . ')',
//                'expectedMetadata' => new Metadata([
//                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
//                        VariableNames::PHPUNIT_TEST_CASE,
//                    ]),
//                ]),
//            ],
//            'has arguments, has failure message containing quotes, assertNotEquals' => [
//                'assertionMethod' => 'assertNotEquals',
//                'arguments' => new MethodArguments([
//                    new LiteralExpression('100'),
//                    new LiteralExpression('\'string\''),
//                ]),
//                'expectedRenderedInvocation' => '{{ PHPUNIT }}->assertNotEquals(' . "\n"
//                    . '    100,' . "\n"
//                    . '    \'string\'' . "\n"
//                    . ')',
//                'expectedMetadata' => new Metadata([
//                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
//                        VariableNames::PHPUNIT_TEST_CASE,
//                    ]),
//                ]),
//            ],
        ];
    }
}
