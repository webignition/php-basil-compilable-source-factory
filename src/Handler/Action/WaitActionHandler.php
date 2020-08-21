<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSource\Body\Body;
use webignition\BasilCompilableSource\Body\BodyInterface;
use webignition\BasilCompilableSource\Expression\CastExpression;
use webignition\BasilCompilableSource\Expression\ComparisonExpression;
use webignition\BasilCompilableSource\Expression\CompositeExpression;
use webignition\BasilCompilableSource\Expression\EncapsulatedExpression;
use webignition\BasilCompilableSource\Expression\LiteralExpression;
use webignition\BasilCompilableSource\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\AccessorDefaultValueFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\ValueAccessorFactory;
use webignition\BasilModels\Action\ActionInterface;

class WaitActionHandler
{
    private const MICROSECONDS_PER_MILLISECOND = 1000;

    private AccessorDefaultValueFactory $accessorDefaultValueFactory;
    private ValueAccessorFactory $valueAccessorFactory;

    public function __construct(
        AccessorDefaultValueFactory $accessorDefaultValueFactory,
        ValueAccessorFactory $valueAccessorFactory
    ) {
        $this->accessorDefaultValueFactory = $accessorDefaultValueFactory;
        $this->valueAccessorFactory = $valueAccessorFactory;
    }

    public static function createHandler(): WaitActionHandler
    {
        return new WaitActionHandler(
            AccessorDefaultValueFactory::createFactory(),
            ValueAccessorFactory::createFactory()
        );
    }

    /**
     * @param ActionInterface $waitAction
     *
     * @return BodyInterface
     *
     * @throws UnsupportedContentException
     */
    public function handle(ActionInterface $waitAction): BodyInterface
    {
        $duration = $waitAction->getValue();

        if (ctype_digit($duration)) {
            $duration = '"' . $duration . '"';
        }

        $durationAccessor = $this->valueAccessorFactory->create($duration);

        $nullCoalescingExpression = new ComparisonExpression(
            $durationAccessor,
            new LiteralExpression((string) ($this->accessorDefaultValueFactory->createInteger($duration) ?? 0)),
            '??'
        );

        $castToIntExpression = new CastExpression($nullCoalescingExpression, 'int');

        $sleepInvocation = new MethodInvocation(
            'usleep',
            [
                new CompositeExpression([
                    new EncapsulatedExpression($castToIntExpression),
                    new LiteralExpression(' * '),
                    new LiteralExpression((string) self::MICROSECONDS_PER_MILLISECOND)
                ]),
            ]
        );

        return Body::createFromExpressions([$sleepInvocation]);
    }
}
