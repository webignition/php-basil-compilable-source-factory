<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\ArgumentFactory;
use webignition\BasilCompilableSourceFactory\CallFactory\PhpUnitCallFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\ComparisonExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\EncapsulatedExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilModels\Model\Assertion\AssertionInterface;

class ScalarExistenceAssertionHandler extends AbstractAssertionHandler
{
    public const ASSERT_TRUE_METHOD = 'assertTrue';
    public const ASSERT_FALSE_METHOD = 'assertFalse';

    private const OPERATOR_TO_ASSERTION_TEMPLATE_MAP = [
        'exists' => self::ASSERT_TRUE_METHOD,
        'not-exists' => self::ASSERT_FALSE_METHOD,
    ];

    public function __construct(
        ArgumentFactory $argumentFactory,
        PhpUnitCallFactory $phpUnitCallFactory,
        private ScalarValueHandler $scalarValueHandler
    ) {
        parent::__construct($argumentFactory, $phpUnitCallFactory);
    }

    public static function createHandler(): self
    {
        return new ScalarExistenceAssertionHandler(
            ArgumentFactory::createFactory(),
            PhpUnitCallFactory::createFactory(),
            ScalarValueHandler::createHandler()
        );
    }

    /**
     * @throws UnsupportedContentException
     */
    public function handle(AssertionInterface $assertion, Metadata $metadata): BodyInterface
    {
        $nullComparisonExpression = new ComparisonExpression(
            $this->scalarValueHandler->handle((string) $assertion->getIdentifier()),
            new LiteralExpression('null'),
            '??'
        );

        $examinedAccessor = new ComparisonExpression(
            new EncapsulatedExpression($nullComparisonExpression),
            new LiteralExpression('null'),
            '!=='
        );

        $assertionStatement = $this->createAssertionStatement(
            $assertion,
            $metadata,
            new MethodArguments([$examinedAccessor])
        );

        return new Body([$assertionStatement]);
    }

    protected function getOperationToAssertionTemplateMap(): array
    {
        return self::OPERATOR_TO_ASSERTION_TEMPLATE_MAP;
    }
}
