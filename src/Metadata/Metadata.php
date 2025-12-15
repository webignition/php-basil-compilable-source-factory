<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Metadata;

use webignition\BasilModels\Model\Assertion\AssertionInterface;
use webignition\BasilModels\Model\Assertion\DerivedValueOperationAssertion;

readonly class Metadata implements \JsonSerializable
{
    public function __construct(
        private AssertionInterface $assertion
    ) {}

    /**
     * @return array{
     *   assertion: string,
     *   source?: string
     * }
     */
    public function jsonSerialize(): array
    {
        $data = [
            'assertion' => (string) $this->assertion,
        ];

        if ($this->assertion instanceof DerivedValueOperationAssertion) {
            $data['source'] = (string) $this->assertion->getSourceStatement();
        }

        return $data;
    }
}
