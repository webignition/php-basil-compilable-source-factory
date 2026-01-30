<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler;

use webignition\BasilCompilableSourceFactory\ArgumentFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Enum\Type;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyContentCollection;
use webignition\BasilCompilableSourceFactory\Model\EmptyLine;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\CastExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ClosureExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\ReturnExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;

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
            $this->argumentFactory->create($serializedElementIdentifier)
        );
    }

    public function handleElementCollection(string $serializedElementIdentifier): ExpressionInterface
    {
        return $this->domCrawlerNavigatorCallFactory->createFindCall(
            $this->argumentFactory->create($serializedElementIdentifier)
        );
    }

    public function handleAttributeValue(
        string $serializedElementIdentifier,
        string $attributeName
    ): ExpressionInterface {
        $findCall = $this->domCrawlerNavigatorCallFactory->createFindOneCall(
            $this->argumentFactory->create($serializedElementIdentifier)
        );

        $elementVariable = Property::asObjectVariable('element');

        $methodCall = new MethodInvocation(
            methodName: 'getAttribute',
            arguments: new MethodArguments([$this->argumentFactory->create($attributeName)]),
            mightThrow: true,
            type: TypeCollection::string(),
            parent: $elementVariable,
        );

        $returnCall = new CastExpression($methodCall, Type::STRING);

        $bodyContent = $this->createBodyContent($elementVariable, $findCall, $returnCall);

        return new ClosureExpression(new Body($bodyContent));
    }

    public function handleElementValue(string $serializedElementIdentifier): ExpressionInterface
    {
        $findCall = $this->domCrawlerNavigatorCallFactory->createFindCall(
            $this->argumentFactory->create($serializedElementIdentifier)
        );

        $elementVariable = Property::asObjectVariable('element');

        $returnCall = new MethodInvocation(
            methodName: 'getValue',
            arguments: new MethodArguments([
                $elementVariable,
            ]),
            mightThrow: false,
            type: TypeCollection::string(),
            parent: Property::asDependency(DependencyName::WEBDRIVER_ELEMENT_INSPECTOR),
        );

        $bodyContent = $this->createBodyContent($elementVariable, $findCall, $returnCall);

        return new ClosureExpression(new Body($bodyContent));
    }

    private function createBodyContent(
        Property $elementVariable,
        ExpressionInterface $findCall,
        ExpressionInterface $returnCall,
    ): BodyContentCollection {
        return new BodyContentCollection()
            ->append(
                new Statement(
                    new AssignmentExpression($elementVariable, $findCall)
                )
            )
            ->append(
                new EmptyLine()
            )
            ->append(
                new Statement(
                    new ReturnExpression($returnCall)
                )
            )
        ;
    }
}
