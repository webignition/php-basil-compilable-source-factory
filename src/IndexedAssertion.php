<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilModels\Model\Assertion\AssertionInterface;

readonly class IndexedAssertion extends IndexedStatement implements AssertionInterface
{
    public function __construct(
        private AssertionInterface $assertion,
        int $index,
    ) {
        parent::__construct($assertion, $index);
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return array_merge(
            parent::jsonSerialize(),
            $this->assertion->jsonSerialize(),
        );
    }

    public function getOperator(): string
    {
        return $this->assertion->getOperator();
    }

    public function equals(AssertionInterface $assertion): bool
    {
        return $this->assertion->equals($assertion);
    }

    public function normalise(): AssertionInterface
    {
        return $this->assertion->normalise();
    }

    public function isComparison(): bool
    {
        return $this->assertion->isComparison();
    }
}
