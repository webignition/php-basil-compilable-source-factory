<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Action;

use webignition\BasilCompilableSource\Block\CodeBlock;
use webignition\BasilCompilableSource\Block\CodeBlockInterface;
use webignition\BasilCompilableSource\Line\CastExpression;
use webignition\BasilCompilableSource\Line\ComparisonExpression;
use webignition\BasilCompilableSource\Line\CompositeExpression;
use webignition\BasilCompilableSource\Line\EncapsulatedExpression;
use webignition\BasilCompilableSource\Line\LiteralExpression;
use webignition\BasilCompilableSource\Line\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSource\Line\Statement\Statement;
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
     * @return CodeBlockInterface
     *
     * @throws UnsupportedContentException
     */
    public function handle(ActionInterface $waitAction): CodeBlockInterface
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

        $sleepInvocation = new Statement(
            new MethodInvocation(
                'usleep',
                [
                    new CompositeExpression([
                        new EncapsulatedExpression($castToIntExpression),
                        new LiteralExpression(' * '),
                        new LiteralExpression((string) self::MICROSECONDS_PER_MILLISECOND)
                    ]),
                ]
            )
        );

        return new CodeBlock([
            $sleepInvocation,
        ]);
    }
}
