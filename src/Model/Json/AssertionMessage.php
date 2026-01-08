<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Json;

use webignition\BasilCompilableSourceFactory\Model\Expression\JsonExpression;
use webignition\BasilModels\Model\Assertion\AssertionInterface;

readonly class AssertionMessage extends JsonExpression
{
    public function __construct(
        AssertionInterface $assertion,
        LiteralInterface $expected,
        LiteralInterface $examined,
    ) {
        parent::__construct([
            'statement' => $assertion->jsonSerialize(),
            'expected' => $expected,
            'examined' => $examined,
        ]);
    }
}
