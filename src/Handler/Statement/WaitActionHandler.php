<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Statement;

use webignition\BasilCompilableSourceFactory\AccessorDefaultValueFactory;
use webignition\BasilCompilableSourceFactory\Enum\Type;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Expression\AssignmentExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\CastExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ComparisonExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\CompositeExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\LiteralExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\NullCoalescerExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\TernaryExpression;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArguments;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\MethodInvocation;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;
use webignition\BasilCompilableSourceFactory\ValueAccessorFactory;
use webignition\BasilModels\Model\Statement\Action\ActionInterface;
use webignition\BasilModels\Model\Statement\StatementInterface;

class WaitActionHandler implements StatementHandlerInterface
{
    private const int MICROSECONDS_PER_MILLISECOND = 1000;

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
    public function handle(StatementInterface $statement): ?StatementHandlerComponents
    {
        if (!$statement instanceof ActionInterface) {
            return null;
        }

        if (!$statement->isWait()) {
            return null;
        }

        $duration = (string) $statement->getValue();
        if (ctype_digit($duration)) {
            $duration = '"' . $duration . '"';
        }

        $durationAccessor = $this->valueAccessorFactory->create($duration);
        $durationVariable = Property::asIntegerVariable('duration');

        $sleepInvocation = new MethodInvocation(
            methodName: 'usleep',
            arguments: new MethodArguments(
                [
                    new CompositeExpression(
                        [
                            $durationVariable,
                            LiteralExpression::void(' * '),
                            LiteralExpression::integer(self::MICROSECONDS_PER_MILLISECOND)
                        ],
                        TypeCollection::integer(),
                    ),
                ]
            ),
            mightThrow: false,
            type: TypeCollection::void(),
        );

        return new StatementHandlerComponents(
            new Statement($sleepInvocation)
        )->withSetup(
            new Body([
                'duration = accessor' => new Statement(
                    new AssignmentExpression(
                        $durationVariable,
                        $durationAccessor,
                    )
                ),
                'duration = duration ?? default' => new Statement(
                    new AssignmentExpression(
                        $durationVariable,
                        new NullCoalescerExpression(
                            $durationVariable,
                            LiteralExpression::integer(
                                $this->accessorDefaultValueFactory->createInteger($duration) ?? 0,
                            )
                        ),
                    )
                ),
                'duration = (int) duration' => new Statement(
                    new AssignmentExpression(
                        $durationVariable,
                        new CastExpression(
                            $durationVariable,
                            Type::INTEGER,
                        )
                    )
                ),
                'duration = duration < 0 ? 0 : duration' => new Statement(
                    new AssignmentExpression(
                        $durationVariable,
                        new TernaryExpression(
                            new ComparisonExpression(
                                $durationVariable,
                                LiteralExpression::integer(0),
                                '<'
                            ),
                            LiteralExpression::integer(0),
                            $durationVariable,
                        ),
                    ),
                ),
            ]),
        );
    }
}
