<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Expression\ArrayExpression\ArrayExpression;
use webignition\BasilCompilableSourceFactory\Model\Expression\ReturnExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Statement\Statement;

class DataProviderMethodDefinition extends MethodDefinition implements DataProviderMethodDefinitionInterface
{
    use HasMetadataTrait;

    /**
     * @var array<mixed>
     */
    private array $data;

    /**
     * @param array<mixed> $data
     */
    public function __construct(string $name, array $data)
    {
        $this->data = $data;

        parent::__construct($name, new Body([
            new Statement(
                new ReturnExpression(
                    ArrayExpression::fromArray($data)
                )
            ),
        ]));

        $this->metadata = new Metadata();
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getArguments(): array
    {
        return [];
    }

    public function getReturnType(): ?string
    {
        return 'array';
    }

    public function getVisibility(): string
    {
        return 'public';
    }

    public function isStatic(): bool
    {
        return false;
    }
}
