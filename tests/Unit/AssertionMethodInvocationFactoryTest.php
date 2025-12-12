<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BasilCompilableSourceFactory\AssertionMethodInvocationFactory;
use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Metadata\Metadata as TestMetaData;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
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
        TestMetaData $metadata,
        MethodArgumentsInterface $arguments,
        string $expectedRenderedInvocation,
        MetadataInterface $expectedMetadata
    ): void {
        $invocation = $this->assertionMethodInvocationFactory->create($assertionMethod, $metadata, $arguments);

        $this->assertRenderResolvable($expectedRenderedInvocation, $invocation);
        $this->assertEquals($expectedMetadata, $invocation->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public static function createDataProvider(): array
    {
        $expectedMetadata = new Metadata(
            variableNames: [
                VariableName::PHPUNIT_TEST_CASE,
            ],
        );

        return [
            'no arguments, assertTrue, assertion contains no quotes' => [
                'assertionMethod' => 'assertTrue',
                'metadata' => new TestMetaData(
                    'step name',
                    (function () {
                        $assertion = \Mockery::mock(AssertionInterface::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('assertion as string')
                        ;

                        return $assertion;
                    })(),
                ),
                'arguments' => new MethodArguments(),
                'expectedRenderedInvocation' => <<<'EOD'
                    {{ PHPUNIT }}->assertTrue(
                        '{
                            \"step\": \"step name\",
                            \"statement\": \"assertion as string\"
                        }'
                    )
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
            'no arguments, assertTrue, assertion contains quotes' => [
                'assertionMethod' => 'assertTrue',
                'metadata' => new TestMetaData(
                    'step name',
                    (function () {
                        $assertion = \Mockery::mock(AssertionInterface::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('\'assertion\' "as" string')
                        ;

                        return $assertion;
                    })(),
                ),
                'arguments' => new MethodArguments(),
                'expectedRenderedInvocation' => <<<'EOD'
                    {{ PHPUNIT }}->assertTrue(
                        '{
                            \"step\": \"step name\",
                            \"statement\": \"\'assertion\' \\\"as\\\" string\"
                        }'
                    )
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
            'has arguments, assertEquals' => [
                'assertionMethod' => 'assertEquals',
                'metadata' => new TestMetaData(
                    'step name',
                    (function () {
                        $assertion = \Mockery::mock(AssertionInterface::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('assertion as string')
                        ;

                        return $assertion;
                    })(),
                ),
                'arguments' => new MethodArguments([
                    new LiteralExpression('100'),
                    new LiteralExpression('\'string\''),
                ]),
                'expectedRenderedInvocation' => <<<'EOD'
                    {{ PHPUNIT }}->assertEquals(
                        100,
                        'string',
                        '{
                            \"step\": \"step name\",
                            \"statement\": \"assertion as string\"
                        }'
                    )
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
            'has arguments, assertNotEquals' => [
                'assertionMethod' => 'assertNotEquals',
                'metadata' => new TestMetaData(
                    'step name',
                    (function () {
                        $assertion = \Mockery::mock(AssertionInterface::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('assertion as string')
                        ;

                        return $assertion;
                    })(),
                ),
                'arguments' => new MethodArguments([
                    new LiteralExpression('100'),
                    new LiteralExpression('\'string\''),
                ]),
                'expectedRenderedInvocation' => <<<'EOD'
                    {{ PHPUNIT }}->assertNotEquals(
                        100,
                        'string',
                        '{
                            \"step\": \"step name\",
                            \"statement\": \"assertion as string\"
                        }'
                    )
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
        ];
    }
}
