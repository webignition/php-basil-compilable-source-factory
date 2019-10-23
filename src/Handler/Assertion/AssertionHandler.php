<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\DelegatorInterface;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Handler\AbstractDelegatingHandler;
use webignition\BasilModel\Assertion\ComparisonAssertionInterface;
use webignition\BasilModel\Assertion\ExaminationAssertionInterface;

class AssertionHandler extends AbstractDelegatingHandler implements DelegatorInterface, HandlerInterface
{
    public static function createHandler(): HandlerInterface
    {
        return new AssertionHandler(
            [
                ExistsComparisonHandler::createHandler(),
                IsComparisonHandler::createHandler(),
                IncludesComparisonHandler::createHandler(),
                MatchesComparisonHandler::createHandler(),
            ]
        );
    }

    public function handles(object $model): bool
    {
        if ($model instanceof ExaminationAssertionInterface || $model instanceof ComparisonAssertionInterface) {
            return null !== $this->findHandler($model);
        }

        return false;
    }
}
