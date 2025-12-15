<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\ArgumentFactory;
use webignition\BasilCompilableSourceFactory\AssertionMethodInvocationFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\ElementIdentifierCallFactory;
use webignition\BasilCompilableSourceFactory\ElementIdentifierSerializer;
use webignition\BasilCompilableSourceFactory\Enum\VariableName as VariableNameEnum;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\DomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\CatchBlock;
use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\TryBlock;
use webignition\BasilCompilableSourceFactory\Model\Block\TryCatch\TryCatchBlock;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\CatchExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ComparisonExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\EncapsulatedExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ExpressionInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ObjectPropertyAccessExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\ObjectMethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\TypeDeclaration\ObjectTypeDeclaration;
use webignition\BasilCompilableSourceFactory\Model\TypeDeclaration\ObjectTypeDeclarationCollection;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilDomIdentifierFactory\Factory as DomIdentifierFactory;
use webignition\BasilModels\Model\Action\ActionInterface;
use webignition\BasilModels\Model\Assertion\Assertion;
use webignition\BasilModels\Model\Assertion\AssertionInterface;
use webignition\BasilModels\Model\Assertion\DerivedValueOperationAssertion;
use webignition\DomElementIdentifier\AttributeIdentifierInterface;
use webignition\DomElementIdentifier\ElementIdentifier;
use webignition\DomElementIdentifier\ElementIdentifierInterface;
use webignition\SymfonyDomCrawlerNavigator\Exception\InvalidLocatorException;

class IdentifierExistenceAssertionHandler extends AbstractAssertionHandler
{
    public const ASSERT_TRUE_METHOD = 'assertTrue';
    public const ASSERT_FALSE_METHOD = 'assertFalse';

    private const OPERATOR_TO_ASSERTION_TEMPLATE_MAP = [
        'exists' => self::ASSERT_TRUE_METHOD,
        'not-exists' => self::ASSERT_FALSE_METHOD,
    ];

    public function __construct(
        AssertionMethodInvocationFactory $assertionMethodInvocationFactory,
        private DomCrawlerNavigatorCallFactory $domCrawlerNavigatorCallFactory,
        private DomIdentifierFactory $domIdentifierFactory,
        private DomIdentifierHandler $domIdentifierHandler,
        private ElementIdentifierCallFactory $elementIdentifierCallFactory,
        private ElementIdentifierSerializer $elementIdentifierSerializer,
        private ArgumentFactory $argumentFactory
    ) {
        parent::__construct($assertionMethodInvocationFactory);
    }

    public static function createHandler(): self
    {
        return new IdentifierExistenceAssertionHandler(
            AssertionMethodInvocationFactory::createFactory(),
            DomCrawlerNavigatorCallFactory::createFactory(),
            DomIdentifierFactory::createFactory(),
            DomIdentifierHandler::createHandler(),
            ElementIdentifierCallFactory::createFactory(),
            ElementIdentifierSerializer::createSerializer(),
            ArgumentFactory::createFactory()
        );
    }

    /**
     * @throws UnsupportedContentException
     */
    public function handle(AssertionInterface $assertion, Metadata $metadata): BodyInterface
    {
        $identifier = $assertion->getIdentifier();

        $assertionStatement = $this->createAssertionStatement(
            $assertion,
            $metadata,
            new MethodArguments([
                $this->createGetBooleanExaminedValueInvocation()
            ])
        );

        $domIdentifier = $this->domIdentifierFactory->createFromIdentifierString((string) $identifier);
        if (null === $domIdentifier) {
            throw new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, $identifier);
        }

        $serializedElementIdentifier = $this->elementIdentifierSerializer->serialize($domIdentifier);
        $elementIdentifierExpression = $this->elementIdentifierCallFactory->createConstructorCall(
            $serializedElementIdentifier
        );

        $examinedElementIdentifierPlaceholder = new ObjectPropertyAccessExpression(
            new VariableDependency(VariableNameEnum::PHPUNIT_TEST_CASE),
            'examinedElementIdentifier'
        );

        $domNavigatorCrawlerCall = $this->createDomCrawlerNavigatorCall(
            $domIdentifier,
            $assertion,
            $examinedElementIdentifierPlaceholder
        );

        $elementSetBooleanExaminedValueInvocation = $this->createSetBooleanExaminedValueInvocation(
            new MethodArguments(
                [
                    $domNavigatorCrawlerCall
                ],
                MethodArgumentsInterface::FORMAT_STACKED
            )
        );

        if (!$domIdentifier instanceof AttributeIdentifierInterface) {
            return new Body([
                new Statement(
                    new AssignmentExpression($examinedElementIdentifierPlaceholder, $elementIdentifierExpression)
                ),
                $this->createNavigatorHasCallTryCatchBlock($elementSetBooleanExaminedValueInvocation),
                $assertionStatement,
            ]);
        }

        $elementIdentifierString = (string) ElementIdentifier::fromAttributeIdentifier($domIdentifier);
        $elementExistsAssertion = new Assertion(
            $elementIdentifierString . ' exists',
            $elementIdentifierString,
            'exists'
        );

        $attributeNullComparisonExpression = new ComparisonExpression(
            $this->domIdentifierHandler->handleAttributeValue(
                $this->elementIdentifierSerializer->serialize($domIdentifier),
                $domIdentifier->getAttributeName()
            ),
            new LiteralExpression('null'),
            '??'
        );

        $attributeSetBooleanExaminedValueInvocation = $this->createSetBooleanExaminedValueInvocation(
            new MethodArguments([
                new ComparisonExpression(
                    new EncapsulatedExpression($attributeNullComparisonExpression),
                    new LiteralExpression('null'),
                    '!=='
                ),
            ])
        );

        return new Body([
            new Statement(
                new AssignmentExpression($examinedElementIdentifierPlaceholder, $elementIdentifierExpression)
            ),
            $this->createNavigatorHasCallTryCatchBlock($elementSetBooleanExaminedValueInvocation),
            $this->createAssertionStatement(
                $elementExistsAssertion,
                $metadata,
                new MethodArguments([
                    $this->createGetBooleanExaminedValueInvocation()
                ])
            ),
            new Statement($attributeSetBooleanExaminedValueInvocation),
            $assertionStatement,
        ]);
    }

    protected function getOperationToAssertionTemplateMap(): array
    {
        return self::OPERATOR_TO_ASSERTION_TEMPLATE_MAP;
    }

    private function createSetBooleanExaminedValueInvocation(MethodArgumentsInterface $arguments): ExpressionInterface
    {
        return $this->createPhpUnitTestCaseObjectMethodInvocation('setBooleanExaminedValue', $arguments);
    }

    private function createGetBooleanExaminedValueInvocation(): ExpressionInterface
    {
        return $this->createPhpUnitTestCaseObjectMethodInvocation('getBooleanExaminedValue');
    }

    private function createDomCrawlerNavigatorCall(
        ElementIdentifierInterface $domIdentifier,
        AssertionInterface $assertion,
        ObjectPropertyAccessExpression $expression
    ): ExpressionInterface {
        $isAttributeIdentifier = $domIdentifier instanceof AttributeIdentifierInterface;
        $isDerivedFromInteractionAction = false;

        if ($assertion instanceof DerivedValueOperationAssertion) {
            $sourceStatement = $assertion->getSourceStatement();

            $isDerivedFromInteractionAction
                = $sourceStatement instanceof ActionInterface && $sourceStatement->isInteraction();
        }

        return $isAttributeIdentifier || $isDerivedFromInteractionAction
                ? $this->domCrawlerNavigatorCallFactory->createHasOneCall($expression)
                : $this->domCrawlerNavigatorCallFactory->createHasCall($expression);
    }

    private function createNavigatorHasCallTryCatchBlock(
        ExpressionInterface $elementSetBooleanExaminedValueInvocation
    ): TryCatchBlock {
        return new TryCatchBlock(
            new TryBlock(
                Body::createFromExpressions([$elementSetBooleanExaminedValueInvocation])
            ),
            new CatchBlock(
                new CatchExpression(
                    new ObjectTypeDeclarationCollection([
                        new ObjectTypeDeclaration(new ClassName(InvalidLocatorException::class))
                    ])
                ),
                Body::createFromExpressions([
                    new ObjectMethodInvocation(
                        new VariableDependency(VariableNameEnum::PHPUNIT_TEST_CASE),
                        'fail',
                        new MethodArguments($this->argumentFactory->create('Invalid locator'))
                    )
                ])
            )
        );
    }
}
