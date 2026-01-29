<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Model\Attribute\DataProviderAttribute;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\EmptyLine;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodDefinition;
use webignition\BasilCompilableSourceFactory\Model\MethodDefinitionInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\SingleLineComment;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;

class MethodDefinitionTest extends AbstractResolvableTestCase
{
    /**
     * @param string[] $arguments
     */
    #[DataProvider('createDataProvider')]
    public function testCreate(
        string $name,
        BodyInterface $body,
        array $arguments,
    ): void {
        $methodDefinition = new MethodDefinition($name, $body, $arguments);

        $this->assertSame($name, $methodDefinition->getName());
        $this->assertEquals($body->getMetadata(), $methodDefinition->getMetadata());
        $this->assertSame($arguments, $methodDefinition->getArguments());
        $this->assertsame(MethodDefinition::VISIBILITY_PUBLIC, $methodDefinition->getVisibility());
        $this->assertFalse($methodDefinition->isStatic());
    }

    /**
     * @return array<mixed>
     */
    public static function createDataProvider(): array
    {
        $body = new Body([]);

        return [
            'no arguments' => [
                'name' => 'noArguments',
                'body' => $body,
                'arguments' => [],
            ],
            'empty arguments' => [
                'name' => 'emptyArguments',
                'body' => $body,
                'arguments' => [],
            ],
            'has arguments' => [
                'name' => 'hasArguments',
                'body' => $body,
                'arguments' => [
                    'arg1',
                    'arg2',
                ],
            ],
        ];
    }

    #[DataProvider('getReturnTypeDataProvider')]
    public function testGetReturnType(MethodDefinition $methodDefinition, TypeCollection $expected): void
    {
        self::assertEquals($expected, $methodDefinition->getReturnType());
    }

    /**
     * @return array<mixed>
     */
    public static function getReturnTypeDataProvider(): array
    {
        return [
            'empty' => [
                'methodDefinition' => new MethodDefinition(
                    'methodName',
                    new Body([]),
                ),
                'expected' => TypeCollection::void(),
            ],
        ];
    }

    #[DataProvider('getMetadataDataProvider')]
    public function testGetMetadata(
        MethodDefinitionInterface $methodDefinition,
        MetadataInterface $expectedMetadata
    ): void {
        $this->assertEquals($expectedMetadata, $methodDefinition->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public static function getMetadataDataProvider(): array
    {
        return [
            'empty' => [
                'methodDefinition' => new MethodDefinition('name', new Body([])),
                'expectedMetadata' => new Metadata(),
            ],
            'lines without metadata' => [
                'methodDefinition' => new MethodDefinition('name', new Body([
                    new EmptyLine(),
                    new SingleLineComment('single line comment'),
                ])),
                'expectedMetadata' => new Metadata(),
            ],
            'lines without metadata with data provider attribute' => [
                'methodDefinition' => new MethodDefinition('name', new Body([
                    new EmptyLine(),
                    new SingleLineComment('single line comment'),
                ]))->withAttribute(new DataProviderAttribute('dataProviderMethod')),
                'expectedMetadata' => new Metadata(
                    classNames: [
                        DataProvider::class,
                    ],
                ),
            ],
            'lines with metadata without data provider attribute' => [
                'methodDefinition' => new MethodDefinition('name', new Body([
                    new Statement(
                        new MethodInvocation(
                            methodName: 'methodName',
                            arguments: new MethodArguments(),
                            mightThrow: false,
                            type: TypeCollection::string(),
                            parent: Property::asDependency(DependencyName::PANTHER_CLIENT),
                        )
                    ),
                    new Statement(
                        new AssignmentExpression(
                            Property::asStringVariable('variable'),
                            new MethodInvocation(
                                methodName: 'methodName',
                                arguments: new MethodArguments(),
                                mightThrow: false,
                                type: TypeCollection::string(),
                            ),
                        )
                    ),
                ])),
                'expectedMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PANTHER_CLIENT,
                    ]
                ),
            ],
            'lines with metadata with data provider attribute' => [
                'methodDefinition' => new MethodDefinition('name', new Body([
                    new Statement(
                        new MethodInvocation(
                            methodName: 'methodName',
                            arguments: new MethodArguments(),
                            mightThrow: false,
                            type: TypeCollection::string(),
                            parent: Property::asDependency(DependencyName::PANTHER_CLIENT),
                        )
                    ),
                    new Statement(
                        new AssignmentExpression(
                            Property::asStringVariable('variable'),
                            new MethodInvocation(
                                methodName: 'methodName',
                                arguments: new MethodArguments(),
                                mightThrow: false,
                                type: TypeCollection::string(),
                            )
                        )
                    ),
                ]))->withAttribute(new DataProviderAttribute('dataProviderMethod')),
                'expectedMetadata' => new Metadata(
                    classNames: [
                        DataProvider::class,
                    ],
                    dependencyNames: [
                        DependencyName::PANTHER_CLIENT,
                    ]
                ),
            ],
        ];
    }

    public function testVisibility(): void
    {
        $methodDefinition = new MethodDefinition('name', new Body([]));
        $this->assertSame(MethodDefinition::VISIBILITY_PUBLIC, $methodDefinition->getVisibility());

        $methodDefinition->setProtected();
        $this->assertSame(MethodDefinition::VISIBILITY_PROTECTED, $methodDefinition->getVisibility());

        $methodDefinition->setPrivate();
        $this->assertSame(MethodDefinition::VISIBILITY_PRIVATE, $methodDefinition->getVisibility());

        $methodDefinition->setPublic();
        $this->assertSame(MethodDefinition::VISIBILITY_PUBLIC, $methodDefinition->getVisibility());
    }

    public function testIsStatic(): void
    {
        $methodDefinition = new MethodDefinition('name', new Body([]));
        $this->assertFalse($methodDefinition->isStatic());

        $methodDefinition->setStatic();
        $this->assertTrue($methodDefinition->isStatic());
    }

    #[DataProvider('renderDataProvider')]
    public function testRender(MethodDefinitionInterface $methodDefinition, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $methodDefinition);
    }

    /**
     * @return array<mixed>
     */
    public static function renderDataProvider(): array
    {
        $emptyProtectedMethod = new MethodDefinition('emptyProtectedMethod', new Body([]));
        $emptyProtectedMethod->setProtected();

        $emptyPrivateMethod = new MethodDefinition('emptyPrivateMethod', new Body([]));
        $emptyPrivateMethod->setPrivate();

        $emptyPublicStaticMethod = new MethodDefinition('emptyPublicStaticMethod', new Body([]));
        $emptyPublicStaticMethod->setStatic();

        return [
            'public, no arguments, no return type, no lines' => [
                'methodDefinition' => new MethodDefinition('emptyPublicMethod', new Body([])),
                'expectedString' => <<<'EOD'
                    public function emptyPublicMethod(): void
                    {
                    
                    }
                    EOD,
            ],
            'protected, no arguments, no return type, no lines' => [
                'methodDefinition' => $emptyProtectedMethod,
                'expectedString' => <<<'EOD'
                    protected function emptyProtectedMethod(): void
                    {
                    
                    }
                    EOD,
            ],
            'private, no arguments, no return type, no lines' => [
                'methodDefinition' => $emptyPrivateMethod,
                'expectedString' => <<<'EOD'
                    private function emptyPrivateMethod(): void
                    {

                    }
                    EOD,
            ],
            'public, has arguments, no return type, no lines' => [
                'methodDefinition' => new MethodDefinition('emptyPublicMethod', new Body([]), [
                    'arg1',
                    'arg2',
                    'arg3',
                ]),
                'expectedString' => <<<'EOD'
                    public function emptyPublicMethod($arg1, $arg2, $arg3): void
                    {
                    
                    }
                    EOD,
            ],
            'public, has arguments, no return type, has lines' => [
                'methodDefinition' => new MethodDefinition(
                    'nameOfMethod',
                    new Body([
                        new SingleLineComment('Assign object method call to $value'),
                        new EmptyLine(),
                        new Statement(
                            new AssignmentExpression(
                                Property::asStringVariable('value'),
                                new MethodInvocation(
                                    methodName: 'methodName',
                                    arguments: new MethodArguments([
                                        LiteralExpression::string('$x'),
                                        LiteralExpression::string('$y'),
                                    ]),
                                    mightThrow: false,
                                    type: TypeCollection::string(),
                                    parent: Property::asDependency(DependencyName::PANTHER_CLIENT),
                                )
                            )
                        ),
                    ]),
                    ['x', 'y']
                ),
                'expectedString' => <<<'EOD'
                    public function nameOfMethod($x, $y): void
                    {
                        // Assign object method call to $value
                    
                        $value = {{ CLIENT }}->methodName($x, $y);
                    }
                    EOD,
            ],
            'public, has arguments, no return type, has lines with trailing newline' => [
                'methodDefinition' => new MethodDefinition(
                    'nameOfMethod',
                    new Body([
                        new SingleLineComment('comment'),
                        new EmptyLine(),
                    ]),
                    ['x', 'y']
                ),
                'expectedString' => <<<'EOD'
                    public function nameOfMethod($x, $y): void
                    {
                        // comment
                    }
                    EOD,
            ],
            'public static, no arguments, no return type, no lines' => [
                'methodDefinition' => $emptyPublicStaticMethod,
                'expectedString' => <<<'EOD'
                    public static function emptyPublicStaticMethod(): void
                    {
                    
                    }
                    EOD,
            ],
            'public, has arguments, no return type, has mutated docblock' => [
                'methodDefinition' => (function () {
                    $methodDefinition = new MethodDefinition(
                        'nameOfMethod',
                        new Body([
                            new SingleLineComment('comment'),
                        ]),
                        ['x', 'y']
                    );

                    return $methodDefinition->withAttribute(
                        new DataProviderAttribute('dataProviderMethodName')
                    );
                })(),
                'expectedString' => <<<'EOD'
                    #[DataProvider('dataProviderMethodName')]
                    public function nameOfMethod($x, $y): void
                    {
                        // comment
                    }
                    EOD,
            ],
            'public, has arguments, no return type, single data provider attribute' => [
                'methodDefinition' => (function () {
                    $methodDefinition = new MethodDefinition(
                        'nameOfMethod',
                        new Body([
                            new SingleLineComment('comment'),
                        ]),
                        ['x', 'y']
                    );

                    return $methodDefinition->withAttribute(
                        new DataProviderAttribute('dataProviderMethodName')
                    );
                })(),
                'expectedString' => <<<'EOD'
                    #[DataProvider('dataProviderMethodName')]
                    public function nameOfMethod($x, $y): void
                    {
                        // comment
                    }
                    EOD,
            ],
            'public, has arguments, no return type, two data provider attributes' => [
                'methodDefinition' => (function () {
                    $methodDefinition = new MethodDefinition(
                        'nameOfMethod',
                        new Body([
                            new SingleLineComment('comment'),
                        ]),
                        ['x', 'y']
                    );

                    $methodDefinition = $methodDefinition->withAttribute(
                        new DataProviderAttribute('dataProviderMethodName1')
                    );

                    return $methodDefinition->withAttribute(
                        new DataProviderAttribute('dataProviderMethodName2')
                    );
                })(),
                'expectedString' => <<<'EOD'
                    #[DataProvider('dataProviderMethodName1')]
                    #[DataProvider('dataProviderMethodName2')]
                    public function nameOfMethod($x, $y): void
                    {
                        // comment
                    }
                    EOD,
            ],
        ];
    }
}
