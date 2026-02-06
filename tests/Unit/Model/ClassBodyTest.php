<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Model\Attribute\DataProviderAttribute;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyContentCollection;
use webignition\BasilCompilableSourceFactory\Model\ClassBody;
use webignition\BasilCompilableSourceFactory\Model\DataProviderMethodDefinition;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodDefinition;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\SingleLineComment;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\StaticObject;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;

class ClassBodyTest extends AbstractResolvableTestCase
{
    #[DataProvider('renderDataProvider')]
    public function testRender(ClassBody $body, string $expectedString): void
    {
        $this->assertRenderResolvable($expectedString, $body);
    }

    /**
     * @return array<mixed>
     */
    public static function renderDataProvider(): array
    {
        return [
            'no methods' => [
                'body' => new ClassBody([]),
                'expectedString' => '',
            ],
            'single empty method' => [
                'body' => new ClassBody([
                    new MethodDefinition('methodName', new Body()),
                ]),
                'expectedString' => <<<'EOD'
                    public function methodName(): void
                    {
                    
                    }
                    EOD,
            ],
            'many methods' => [
                'body' => new ClassBody([
                    new MethodDefinition(
                        'stepOne',
                        new Body(
                            new BodyContentCollection()
                                ->append(
                                    new SingleLineComment('click $"a"')
                                )
                                ->append(
                                    new Statement(
                                        new AssignmentExpression(
                                            Property::asStringVariable('statement'),
                                            new MethodInvocation(
                                                methodName: 'createAction',
                                                arguments: new MethodArguments([
                                                    LiteralExpression::string('\'$"a" exists\''),
                                                ]),
                                                mightThrow: false,
                                                type: TypeCollection::string(),
                                                parent: new StaticObject('Acme\Statement'),
                                            )
                                        )
                                    )
                                )
                                ->append(
                                    new Statement(
                                        new AssignmentExpression(
                                            Property::asStringVariable('currentStatement'),
                                            Property::asStringVariable('statement')
                                        )
                                    )
                                )
                        )
                    ),
                    new MethodDefinition(
                        'stepTwo',
                        new Body(
                            new BodyContentCollection()
                                ->append(
                                    new SingleLineComment('click $"b"')
                                )
                                ->append(
                                    new Statement(
                                        new AssignmentExpression(
                                            Property::asStringVariable('statement'),
                                            new MethodInvocation(
                                                methodName: 'createAction',
                                                arguments: new MethodArguments([
                                                    LiteralExpression::string('\'$"b" exists\''),
                                                ]),
                                                mightThrow: false,
                                                type: TypeCollection::string(),
                                                parent: new StaticObject('Acme\Statement'),
                                            )
                                        )
                                    )
                                )
                                ->append(
                                    new Statement(
                                        new AssignmentExpression(
                                            Property::asStringVariable('currentStatement'),
                                            Property::asStringVariable('statement')
                                        )
                                    )
                                )
                        )
                    ),
                ]),
                'expectedString' => <<<'EOD'
                    public function stepOne(): void
                    {
                        // click $"a"
                        $statement = Statement::createAction('$"a" exists');
                        $currentStatement = $statement;
                    }
                    
                    public function stepTwo(): void
                    {
                        // click $"b"
                        $statement = Statement::createAction('$"b" exists');
                        $currentStatement = $statement;
                    }
                    EOD,
            ],
            'many methods, with data provider' => [
                'body' => new ClassBody([
                    (function () {
                        $methodDefinition = new MethodDefinition(
                            'stepOne',
                            new Body(
                                new BodyContentCollection()
                                    ->append(
                                        new SingleLineComment('click $"a"')
                                    )
                                    ->append(
                                        new Statement(
                                            new AssignmentExpression(
                                                Property::asStringVariable('statement'),
                                                new MethodInvocation(
                                                    methodName: 'createAction',
                                                    arguments: new MethodArguments([
                                                        LiteralExpression::string('\'$"a" exists\''),
                                                    ]),
                                                    mightThrow: false,
                                                    type: TypeCollection::string(),
                                                    parent: new StaticObject('Acme\Statement'),
                                                )
                                            )
                                        )
                                    )
                                    ->append(
                                        new Statement(
                                            new AssignmentExpression(
                                                Property::asStringVariable('currentStatement'),
                                                Property::asStringVariable('statement')
                                            )
                                        )
                                    )
                            ),
                            [
                                'x', 'y',
                            ]
                        );

                        return $methodDefinition->withAttribute(
                            new DataProviderAttribute('stepOneDataProvider')
                        );
                    })(),
                    new DataProviderMethodDefinition('stepOneDataProvider', [
                        0 => [
                            'x' => '1',
                            'y' => '2',
                        ],
                        1 => [
                            'x' => '3',
                            'y' => '4',
                        ],
                    ]),
                    new MethodDefinition(
                        'stepTwo',
                        new Body(
                            new BodyContentCollection()
                                ->append(
                                    new SingleLineComment('click $"b"')
                                )
                                ->append(
                                    new Statement(
                                        new AssignmentExpression(
                                            Property::asStringVariable('statement'),
                                            new MethodInvocation(
                                                methodName: 'createAction',
                                                arguments: new MethodArguments([
                                                    LiteralExpression::string('\'$"b" exists\''),
                                                ]),
                                                mightThrow: false,
                                                type: TypeCollection::string(),
                                                parent: new StaticObject('Acme\Statement'),
                                            )
                                        )
                                    )
                                )
                                ->append(
                                    new Statement(
                                        new AssignmentExpression(
                                            Property::asStringVariable('currentStatement'),
                                            Property::asStringVariable('statement')
                                        )
                                    )
                                )
                        ),
                    ),
                ]),
                'expectedString' => <<<'EOD'
                    #[DataProvider('stepOneDataProvider')]
                    public function stepOne($x, $y): void
                    {
                        // click $"a"
                        $statement = Statement::createAction('$"a" exists');
                        $currentStatement = $statement;
                    }
                    
                    public static function stepOneDataProvider(): array
                    {
                        return [
                            '0' => [
                                'x' => '1',
                                'y' => '2',
                            ],
                            '1' => [
                                'x' => '3',
                                'y' => '4',
                            ],
                        ];
                    }
                    
                    public function stepTwo(): void
                    {
                        // click $"b"
                        $statement = Statement::createAction('$"b" exists');
                        $currentStatement = $statement;
                    }
                    EOD,
            ],
        ];
    }
}
