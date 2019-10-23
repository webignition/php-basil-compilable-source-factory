<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Transpiler\Assertion;

use webignition\BasilCompilableSourceFactory\DelegatorInterface;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Transpiler\AbstractDelegatingTranspiler;
use webignition\BasilModel\Assertion\ComparisonAssertionInterface;
use webignition\BasilModel\Assertion\ExaminationAssertionInterface;

class AssertionTranspiler extends AbstractDelegatingTranspiler implements DelegatorInterface, HandlerInterface
{
    public static function createTranspiler(): AssertionTranspiler
    {
        return new AssertionTranspiler(
            [
                ExistsComparisonTranspiler::createTranspiler(),
                IsComparisonTranspiler::createTranspiler(),
                IncludesComparisonTranspiler::createTranspiler(),
                MatchesComparisonTranspiler::createTranspiler(),
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
