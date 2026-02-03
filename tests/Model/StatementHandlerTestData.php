<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Model;

use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyInterface;
use webignition\BasilCompilableSourceFactory\Tests\Services\ResolvedVariableNames as ResolvedNames;
use webignition\BasilModels\Model\Statement\StatementInterface;

class StatementHandlerTestData
{
    private readonly string $fixture;
    private readonly StatementInterface $statement;

    /**
     * @var array<value-of<DependencyName>, ResolvedNames::*>
     */
    private array $additionalVariableIdentifiers = [];

    private ?BodyInterface $beforeTest = null;
    private ?BodyInterface $afterTest = null;

    public function __construct(
        string $fixture,
        StatementInterface $statement,
    ) {
        $this->fixture = $fixture;
        $this->statement = $statement;
    }

    public function getFixture(): string
    {
        return $this->fixture;
    }

    public function getStatement(): StatementInterface
    {
        return $this->statement;
    }

    /**
     * @return array<value-of<DependencyName>, ResolvedNames::*>
     */
    public function getAdditionalVariableIdentifiers(): array
    {
        return $this->additionalVariableIdentifiers;
    }

    public function withBeforeTest(BodyInterface $beforeTest): self
    {
        $new = clone $this;
        $new->beforeTest = $beforeTest;

        return $new;
    }

    public function getBeforeTest(): ?BodyInterface
    {
        return $this->beforeTest;
    }

    public function withAfterTest(BodyInterface $afterTest): self
    {
        $new = clone $this;
        $new->afterTest = $afterTest;

        return $new;
    }

    public function getAfterTest(): ?BodyInterface
    {
        return $this->afterTest;
    }
}
