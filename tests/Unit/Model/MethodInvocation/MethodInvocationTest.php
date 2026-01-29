<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Model\MethodInvocation;

use PHPUnit\Framework\Attributes\DataProvider;
use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\StaticObject;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;
use webignition\BasilCompilableSourceFactory\Tests\Unit\Model\AbstractResolvableTestCase;

class MethodInvocationTest extends AbstractResolvableTestCase
{
    #[DataProvider('getMetadataDataProvider')]
    public function testGetMetadata(MethodInvocation $methodInvocation, Metadata $expected): void
    {
        self::assertEquals($expected, $methodInvocation->getMetadata());
    }

    /**
     * @return array<mixed>
     */
    public static function getMetadataDataProvider(): array
    {
        return [
            'no parent, no arguments' => [
                'methodInvocation' => new MethodInvocation(
                    methodName: 'methodName',
                    arguments: new MethodArguments(),
                    mightThrow: false,
                    type: TypeCollection::string(),
                ),
                'expected' => new Metadata(),
            ],
            'no parent, has arguments, no arguments have metadata' => [
                'methodInvocation' => new MethodInvocation(
                    methodName: 'methodName',
                    arguments: new MethodArguments([
                        LiteralExpression::string('"literal string"')
                    ]),
                    mightThrow: false,
                    type: TypeCollection::string(),
                ),
                'expected' => new Metadata(),
            ],
            'no parent, has arguments, arguments have metadata' => [
                'methodInvocation' => new MethodInvocation(
                    methodName: 'methodName',
                    arguments: new MethodArguments([
                        Property::asEnum(
                            new ClassName(DependencyName::class),
                            DependencyName::ENVIRONMENT_VARIABLE_ARRAY->name,
                            TypeCollection::string(),
                        ),
                        Property::asDependency(DependencyName::MESSAGE_FACTORY),
                    ]),
                    mightThrow: false,
                    type: TypeCollection::string(),
                ),
                'expected' => new Metadata(
                    classNames: [
                        DependencyName::class,
                    ],
                    dependencyNames: [
                        DependencyName::MESSAGE_FACTORY,
                    ]
                ),
            ],
            'has parent without metadata, no arguments' => [
                'methodInvocation' => new MethodInvocation(
                    methodName: 'methodName',
                    arguments: new MethodArguments(),
                    mightThrow: false,
                    type: TypeCollection::string(),
                    parent: Property::asObjectVariable('parent')
                ),
                'expected' => new Metadata(),
            ],
            'has parent with metadata, no arguments' => [
                'methodInvocation' => new MethodInvocation(
                    methodName: 'methodName',
                    arguments: new MethodArguments(),
                    mightThrow: false,
                    type: TypeCollection::string(),
                    parent: new MethodInvocation(
                        methodName: 'parentMethodName',
                        arguments: new MethodArguments([
                            Property::asEnum(
                                new ClassName(DependencyName::class),
                                DependencyName::ENVIRONMENT_VARIABLE_ARRAY->name,
                                type: TypeCollection::string(),
                            ),
                            Property::asDependency(DependencyName::MESSAGE_FACTORY),
                        ]),
                        mightThrow: true,
                        type: TypeCollection::string(),
                    ),
                ),
                'expected' => new Metadata(
                    classNames: [
                        DependencyName::class,
                    ],
                    dependencyNames: [
                        DependencyName::MESSAGE_FACTORY,
                    ]
                ),
            ],
            'has parent with metadata, has arguments with different metadata' => [
                'methodInvocation' => new MethodInvocation(
                    methodName: 'methodName',
                    arguments: new MethodArguments([
                        Property::asClassConstant(
                            new ClassName(VariableName::class),
                            'EXPECTED_VALUE',
                            TypeCollection::string(),
                        ),
                        Property::asDependency(DependencyName::PHPUNIT_TEST_CASE),
                    ]),
                    mightThrow: false,
                    type: TypeCollection::string(),
                    parent: new MethodInvocation(
                        methodName: 'parentMethodName',
                        arguments: new MethodArguments([
                            Property::asEnum(
                                new ClassName(DependencyName::class),
                                DependencyName::ENVIRONMENT_VARIABLE_ARRAY->name,
                                TypeCollection::string(),
                            ),
                            Property::asDependency(DependencyName::MESSAGE_FACTORY),
                        ]),
                        mightThrow: true,
                        type: TypeCollection::string(),
                    ),
                ),
                'expected' => new Metadata(
                    classNames: [
                        VariableName::class,
                        DependencyName::class,
                    ],
                    dependencyNames: [
                        DependencyName::MESSAGE_FACTORY,
                        DependencyName::PHPUNIT_TEST_CASE,
                    ]
                ),
            ],
        ];
    }

    #[DataProvider('renderDataProvider')]
    public function testRender(MethodInvocation $methodInvocation, string $expected): void
    {
        self::assertRenderResolvable($expected, $methodInvocation);
    }

    /**
     * @return array<mixed>
     */
    public static function renderDataProvider(): array
    {
        return [
            'no parent, no arguments' => [
                'methodInvocation' => new MethodInvocation(
                    methodName: 'methodName',
                    arguments: new MethodArguments(),
                    mightThrow: false,
                    type: TypeCollection::string(),
                ),
                'expected' => 'methodName()',
            ],
            'no parent, no arguments, error-suppressed' => [
                'methodInvocation' => new MethodInvocation(
                    methodName: 'methodName',
                    arguments: new MethodArguments(),
                    mightThrow: false,
                    type: TypeCollection::string(),
                )->setIsErrorSuppressed(),
                'expected' => '@methodName()',
            ],
            'no parent, has arguments, inline' => [
                'methodInvocation' => new MethodInvocation(
                    methodName: 'methodName',
                    arguments: new MethodArguments([
                        LiteralExpression::integer(1),
                        LiteralExpression::string("\\'single-quoted value\\'"),
                    ]),
                    mightThrow: false,
                    type: TypeCollection::string(),
                ),
                'expected' => "methodName(1, \\'single-quoted value\\')",
            ],
            'no parent, has arguments, stacked' => [
                'methodInvocation' => new MethodInvocation(
                    methodName: 'methodName',
                    arguments: new MethodArguments(
                        [
                            LiteralExpression::integer(1),
                            LiteralExpression::string("\\'single-quoted value\\'"),
                        ],
                        MethodArgumentsInterface::FORMAT_STACKED
                    ),
                    mightThrow: false,
                    type: TypeCollection::string(),
                ),
                'expected' => "methodName(\n"
                    . "    1,\n"
                    . "    \\'single-quoted value\\',\n"
                    . ')',
            ],
            'has dependency as parent, no arguments' => [
                'methodInvocation' => new MethodInvocation(
                    methodName: 'methodName',
                    arguments: new MethodArguments(),
                    mightThrow: false,
                    type: TypeCollection::string(),
                    parent: Property::asDependency(DependencyName::PANTHER_CLIENT),
                ),
                'expected' => '{{ CLIENT }}->methodName()',
            ],
            'has dependency as parent, no arguments, error-suppressed' => [
                'methodInvocation' => new MethodInvocation(
                    methodName: 'methodName',
                    arguments: new MethodArguments(),
                    mightThrow: false,
                    type: TypeCollection::string(),
                    parent: Property::asDependency(DependencyName::PANTHER_CLIENT),
                )->setIsErrorSuppressed(),
                'expected' => '@{{ CLIENT }}->methodName()',
            ],
            'has static object as parent, no arguments' => [
                'methodInvocation' => new MethodInvocation(
                    methodName: 'methodName',
                    arguments: new MethodArguments(),
                    mightThrow: false,
                    type: TypeCollection::string(),
                    parent: new StaticObject('parent'),
                ),
                'expected' => 'parent::methodName()',
            ],
            'has dependency as parent, has arguments inline' => [
                'methodInvocation' => new MethodInvocation(
                    methodName: 'methodName',
                    arguments: new MethodArguments([
                        LiteralExpression::integer(1),
                        LiteralExpression::string("\\'single-quoted value\\'"),
                    ]),
                    mightThrow: false,
                    type: TypeCollection::string(),
                    parent: Property::asDependency(DependencyName::PANTHER_CLIENT),
                ),
                'expected' => <<<'EOD'
                    {{ CLIENT }}->methodName(1, \'single-quoted value\')
                    EOD
            ],
            'has dependency as parent,has arguments, stacked' => [
                'methodInvocation' => new MethodInvocation(
                    methodName: 'methodName',
                    arguments: new MethodArguments(
                        [
                            LiteralExpression::integer(1),
                            LiteralExpression::string("\\'single-quoted value\\'"),
                        ],
                        MethodArgumentsInterface::FORMAT_STACKED,
                    ),
                    mightThrow: false,
                    type: TypeCollection::string(),
                    parent: Property::asDependency(DependencyName::PANTHER_CLIENT),
                ),
                'expected' => <<<'EOD'
                    {{ CLIENT }}->methodName(
                        1,
                        \'single-quoted value\',
                    )
                    EOD
            ],
            'variable name as parent' => [
                'methodInvocation' => new MethodInvocation(
                    methodName: 'methodName',
                    arguments: new MethodArguments(),
                    mightThrow: false,
                    type: TypeCollection::string(),
                    parent: Property::asObjectVariable('object'),
                ),
                'expected' => '$object->methodName()',
            ],
            'method invocation as parent' => [
                'methodInvocation' => new MethodInvocation(
                    methodName: 'methodName',
                    arguments: new MethodArguments(),
                    mightThrow: false,
                    type: TypeCollection::string(),
                    parent: new MethodInvocation(
                        methodName: 'parentMethodName',
                        arguments: new MethodArguments(),
                        mightThrow: false,
                        type: TypeCollection::string(),
                    ),
                ),
                'expected' => 'parentMethodName()->methodName()',
            ],
            'object returned from object method call' => [
                'methodInvocation' => new MethodInvocation(
                    methodName: 'outerMethodName',
                    arguments: new MethodArguments(),
                    mightThrow: false,
                    type: TypeCollection::string(),
                    parent: new MethodInvocation(
                        methodName: 'innerMethodName',
                        arguments: new MethodArguments(),
                        mightThrow: false,
                        type: TypeCollection::string(),
                        parent: Property::asDependency(DependencyName::PANTHER_CLIENT),
                    ),
                ),
                'expected' => '{{ CLIENT }}->innerMethodName()->outerMethodName()',
            ],
        ];
    }
}
