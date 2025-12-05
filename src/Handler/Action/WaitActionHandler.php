<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSourceFactory\AccessorDefaultValueFactory;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Model\Expression\CastExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ComparisonExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\CompositeExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\EncapsulatedExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\ValueAccessorFactory;
use webignition\BasilModels\Model\Action\ActionInterface;

class WaitActionHandler
{
    private const MICROSECONDS_PER_MILLISECOND = 1000;

    public function __construct(
        private AccessorDefaultValueFactory $accessorDefaultValueFactory,
        private ValueAccessorFactory $valueAccessorFactory
    ) {}

    public static function createHandler(): WaitActionHandler
    {
        return new WaitActionHandler(
            AccessorDefaultValueFactory::createFactory(),
            ValueAccessorFactory::createFactory()
        );
    }

    /**
     * @throws UnsupportedContentException
     */
    public function handle(ActionInterface $waitAction): BodyInterface
    {
        $duration = (string) $waitAction->getValue();
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
            new MethodArguments(
                [
                    new CompositeExpression([
                        new EncapsulatedExpression($castToIntExpression),
                        new LiteralExpression(' * '),
                        new LiteralExpression((string) self::MICROSECONDS_PER_MILLISECOND)
                    ]),
                ]
            )
        );

        return Body::createFromExpressions([$sleepInvocation]);
    }
}
