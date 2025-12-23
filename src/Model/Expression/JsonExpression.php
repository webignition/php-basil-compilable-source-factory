<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Expression;

use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;

readonly class JsonExpression implements ExpressionInterface, \JsonSerializable
{
    /**
     * @var array<mixed>
     */
    private array $data;

    /**
     * @param array<mixed> $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getTemplate(): string
    {
        return '\'{{ serialized_json }}\'';
    }

    public function getContext(): array
    {
        return [
            'serialized_json' => addcslashes((string) json_encode($this->data, JSON_PRETTY_PRINT), "'\\"),
        ];
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata();
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
