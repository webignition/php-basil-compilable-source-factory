<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler;

use webignition\BasilCompilableSourceFactory\ArgumentFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\Enum\VariableName as VariableNameEnum;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\EmptyLine;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ClosureExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\ReturnExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\Model\VariableName;

class DomIdentifierHandler
{
    public function __construct(
        private DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory,
        private ArgumentFactory $argumentFactory
    ) {}

    public static function createHandler(): DomIdentifierHandler
    {
        return new DomIdentifierHandler(
            DomCrawlerNavigatorCallFactory::createFactory(),
            ArgumentFactory::createFactory()
        );
    }

    public function handleElement(string $serializedElementIdentifier): ExpressionInterface
    {
        return $this->domCrawlerNavigatorCallFactory->createFindOneCall(
            $this->argumentFactory->createSingular($serializedElementIdentifier)
        );
    }

    public function handleElementCollection(string $serializedElementIdentifier): ExpressionInterface
    {
        return $this->domCrawlerNavigatorCallFactory->createFindCall(
            $this->argumentFactory->createSingular($serializedElementIdentifier)
        );
    }

    public function handleAttributeValue(
        string $serializedElementIdentifier,
        string $attributeName
    ): ExpressionInterface {
        $findCall = $this->domCrawlerNavigatorCallFactory->createFindOneCall(
            $this->argumentFactory->createSingular($serializedElementIdentifier)
        );

        $elementPlaceholder = new VariableName('element');

        $closureExpressionStatements = [
            new Statement(
                new AssignmentExpression($elementPlaceholder, $findCall)
            ),
            new EmptyLine(),
            new Statement(
                new ReturnExpression(
                    new ObjectMethodInvocation(
                        $elementPlaceholder,
                        'getAttribute',
                        new MethodArguments($this->argumentFactory->create($attributeName))
                    )
                )
            ),
        ];

        return new ClosureExpression(new Body($closureExpressionStatements));
    }

    public function handleElementValue(string $serializedElementIdentifier): ExpressionInterface
    {
        $findCall = $this->domCrawlerNavigatorCallFactory->createFindCall(
            $this->argumentFactory->createSingular($serializedElementIdentifier)
        );

        $elementPlaceholder = new VariableName('element');

        $closureExpressionStatements = [
            new Statement(
                new AssignmentExpression($elementPlaceholder, $findCall)
            ),
            new EmptyLine(),
            new Statement(
                new ReturnExpression(
                    new ObjectMethodInvocation(
                        new VariableDependency(VariableNameEnum::WEBDRIVER_ELEMENT_INSPECTOR),
                        'getValue',
                        new MethodArguments([
                            $elementPlaceholder,
                        ])
                    )
                )
            )
        ];

        return new ClosureExpression(new Body($closureExpressionStatements));
    }
}
