<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasilCompilableSourceFactory\ArgumentFactory;
use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ClosureExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ReturnExpression;
use webignition\BasilCompilableSourceFactory\Model\IsAssigneeInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\Statement\StatementInterface;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;

class StatementFactory
{
    public static function createAssertBrowserTitle(string $expectedTitle): StatementInterface
    {
        $argumentFactory = ArgumentFactory::createFactory();

        return new Statement(
            new MethodInvocation(
                methodName: 'assertSame',
                arguments: new MethodArguments([
                    $argumentFactory->create($expectedTitle),
                    new MethodInvocation(
                        methodName: 'getTitle',
                        arguments: new MethodArguments(),
                        mightThrow: false,
                        type: TypeCollection::string(),
                        parent: Property::asDependency(DependencyName::PANTHER_CLIENT),
                    ),
                ]),
                mightThrow: false,
                type: TypeCollection::void(),
                parent: Property::asDependency(DependencyName::PHPUNIT_TEST_CASE),
            )
        );
    }

    public static function createCrawlerFilterCallForElement(
        string $selector,
        IsAssigneeInterface $placeholder
    ): StatementInterface {
        $elementVariable = Property::asObjectVariable('element');
        $argumentFactory = ArgumentFactory::createFactory();

        return new Statement(
            new AssignmentExpression(
                $placeholder,
                new ClosureExpression(
                    new Body([
                        new Statement(
                            new AssignmentExpression(
                                $elementVariable,
                                new MethodInvocation(
                                    methodName: 'filter',
                                    arguments: new MethodArguments([$argumentFactory->create($selector)]),
                                    mightThrow: false,
                                    type: TypeCollection::object(),
                                    parent: Property::asDependency(DependencyName::PANTHER_CRAWLER),
                                )
                            )
                        ),
                        new Statement(
                            new ReturnExpression(
                                new MethodInvocation(
                                    methodName: 'getElement',
                                    arguments: new MethodArguments([$argumentFactory->create('0')]),
                                    mightThrow: false,
                                    type: TypeCollection::object(),
                                    parent: $elementVariable,
                                )
                            )
                        ),
                    ]),
                    TypeCollection::object(),
                )
            )
        );
    }

    public static function createAssertSame(string $expected, string $actual): StatementInterface
    {
        return self::createAssertExpectedActual('assertSame', $expected, $actual);
    }

    public static function createAssertEquals(string $expected, string $actual): StatementInterface
    {
        return self::createAssertExpectedActual('assertEquals', $expected, $actual);
    }

    public static function createCrawlerActionCallForElement(string $selector, string $action): StatementInterface
    {
        $elementVariable = Property::asObjectVariable('element');
        $argumentFactory = ArgumentFactory::createFactory();

        return new Statement(
            new ClosureExpression(
                new Body([
                    new Statement(
                        new AssignmentExpression(
                            $elementVariable,
                            new MethodInvocation(
                                methodName: 'filter',
                                arguments: new MethodArguments([$argumentFactory->create($selector)]),
                                mightThrow: false,
                                type: TypeCollection::object(),
                                parent: Property::asDependency(DependencyName::PANTHER_CRAWLER),
                            )
                        )
                    ),
                    new Statement(
                        new AssignmentExpression(
                            $elementVariable,
                            new MethodInvocation(
                                methodName: 'getElement',
                                arguments: new MethodArguments([$argumentFactory->create('0')]),
                                mightThrow: false,
                                type: TypeCollection::object(),
                                parent: $elementVariable,
                            )
                        )
                    ),
                    new Statement(
                        new MethodInvocation(
                            methodName: $action,
                            arguments: new MethodArguments(),
                            mightThrow: false,
                            type: TypeCollection::object(),
                            parent: $elementVariable,
                        )
                    ),
                ]),
                TypeCollection::object(),
            )
        );
    }

    public static function createClientAction(string $action): StatementInterface
    {
        return new Statement(
            new MethodInvocation(
                methodName: $action,
                arguments: new MethodArguments(),
                mightThrow: false,
                type: TypeCollection::object(),
                parent: Property::asDependency(DependencyName::PANTHER_CLIENT),
            )
        );
    }

    public static function createCrawlerFilterCall(
        string $selector,
        IsAssigneeInterface $placeholder
    ): StatementInterface {
        $argumentFactory = ArgumentFactory::createFactory();

        return new Statement(
            new AssignmentExpression(
                $placeholder,
                new MethodInvocation(
                    methodName: 'filter',
                    arguments: new MethodArguments([$argumentFactory->create($selector)]),
                    mightThrow: false,
                    type: TypeCollection::object(),
                    parent: Property::asDependency(DependencyName::PANTHER_CRAWLER),
                )
            )
        );
    }

    public static function createAssertFalse(string $actual): StatementInterface
    {
        return new Statement(
            new MethodInvocation(
                methodName: 'assertFalse',
                arguments: new MethodArguments([
                    LiteralExpression::string($actual),
                ]),
                mightThrow: false,
                type: TypeCollection::void(),
                parent: Property::asDependency(DependencyName::PHPUNIT_TEST_CASE),
            )
        );
    }

    public static function createAssertTrue(string $actual): StatementInterface
    {
        return new Statement(
            new MethodInvocation(
                methodName: 'assertTrue',
                arguments: new MethodArguments([
                    LiteralExpression::string($actual)
                ]),
                mightThrow: false,
                type: TypeCollection::void(),
                parent: Property::asDependency(DependencyName::PHPUNIT_TEST_CASE),
            )
        );
    }

    public static function createAssertCount(string $expected, string $actual): StatementInterface
    {
        return self::createAssertExpectedActual('assertCount', $expected, $actual);
    }

    public static function createAssertInstanceOf(string $expected, string $actual): StatementInterface
    {
        return self::createAssertExpectedActual('assertInstanceOf', $expected, $actual);
    }

    private static function createAssertExpectedActual(
        string $methodName,
        string $expected,
        string $actual
    ): StatementInterface {
        return new Statement(
            new MethodInvocation(
                methodName: $methodName,
                arguments: new MethodArguments([
                    LiteralExpression::string($expected),
                    LiteralExpression::string($actual),
                ]),
                mightThrow: false,
                type: TypeCollection::void(),
                parent: Property::asDependency(DependencyName::PHPUNIT_TEST_CASE),
            )
        );
    }
}
