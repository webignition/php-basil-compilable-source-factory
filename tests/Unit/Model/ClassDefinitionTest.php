<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\ClassBody;
use webignition\BasilCompilableSourceFactory\Model\ClassDefinition;
use webignition\BasilCompilableSourceFactory\Model\ClassDefinitionInterface;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\ClassSignature;
use webignition\BasilCompilableSourceFactory\Model\EmptyLine;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodDefinition;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\SingleLineComment;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;

class ClassDefinitionTest extends AbstractResolvableTestCase
{
    public function testCreate(): void
    {
        $signature = new ClassSignature('ClassName');
        $body = new ClassBody([]);

        $classDefinition = new ClassDefinition($signature, $body);

        self::assertSame($signature, $classDefinition->getSignature());
        self::assertSame($body, $classDefinition->getBody());
    }

    #[DataProvider('getMetadataDataProvider')]
    public function testGetMetadata(
        ClassDefinitionInterface $classDefinition,
        MetadataInterface $expectedMetadata
    ): void {
        $this->assertEquals($expectedMetadata, $classDefinition->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public static function getMetadataDataProvider(): array
    {
        return [
            'empty' => [
                'classDefinition' => new ClassDefinition(
                    new ClassSignature('className'),
                    new ClassBody([])
                ),
                'expectedMetadata' => new Metadata(),
            ],
            'methods without metadata' => [
                'classDefinition' => new ClassDefinition(
                    new ClassSignature('className'),
                    new ClassBody([
                        new MethodDefinition('name', new Body([
                            new EmptyLine(),
                            new SingleLineComment('single line comment'),
                        ])),
                    ])
                ),
                'expectedMetadata' => new Metadata(),
            ],
            'methods with metadata' => [
                'classDefinition' => new ClassDefinition(
                    new ClassSignature('className'),
                    new ClassBody([
                        new MethodDefinition('name', new Body([
                            new Statement(
                                new MethodInvocation(
                                    methodName: 'methodName',
                                    arguments: new MethodArguments(),
                                    mightThrow: false,
                                    parent: Property::asDependency(DependencyName::PANTHER_CLIENT),
                                )
                            ),
                            new Statement(
                                new AssignmentExpression(
                                    Property::asVariable('variable'),
                                    new MethodInvocation(
                                        methodName: 'methodName',
                                        arguments: new MethodArguments(),
                                        mightThrow: false,
                                    )
                                )
                            )
                        ])),
                    ])
                ),
                'expectedMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PANTHER_CLIENT,
                    ],
                ),
            ],
        ];
    }

    #[DataProvider('renderDataProvider')]
    public function testRender(ClassDefinitionInterface $classDefinition, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $classDefinition);
    }

    /**
     * @return array<mixed>
     */
    public static function renderDataProvider(): array
    {
        return [
            'no methods, no base class' => [
                'classDefinition' => new ClassDefinition(
                    new ClassSignature('NameOfClass'),
                    new ClassBody([])
                ),
                'expectedString' => 'class NameOfClass' . "\n"
                    . '{}'
            ],
            'no methods, base class in root namespace' => [
                'classDefinition' => new ClassDefinition(
                    new ClassSignature(
                        'NameOfClass',
                        new ClassName('TestCase')
                    ),
                    new ClassBody([])
                ),
                'expectedString' => 'class NameOfClass extends \TestCase' . "\n"
                    . '{}'
            ],
            'no methods, base class in non-root namespace' => [
                'classDefinition' => new ClassDefinition(
                    new ClassSignature(
                        'NameOfClass',
                        new ClassName(TestCase::class)
                    ),
                    new ClassBody([])
                ),
                'expectedString' => 'use PHPUnit\Framework\TestCase;' . "\n"
                    . "\n"
                    . 'class NameOfClass extends TestCase' . "\n"
                    . '{}'
            ],
            'has method' => [
                'classDefinition' => new ClassDefinition(
                    new ClassSignature(
                        'NameOfClass',
                        new ClassName('TestCase')
                    ),
                    new ClassBody([
                        new MethodDefinition('methodName', new Body([])),
                    ])
                ),
                'expectedString' => 'class NameOfClass extends \TestCase' . "\n"
                    . '{' . "\n"
                    . '    public function methodName()' . "\n"
                    . '    {' . "\n\n"
                    . '    }' . "\n"
                    . '}'
            ],
        ];
    }
}
