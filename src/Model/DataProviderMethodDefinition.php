<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

use webignition\BasilCompilableSourceFactory\Model\Body\Body;
use webignition\BasilCompilableSourceFactory\Model\Body\BodyContentCollection;
use webignition\BasilCompilableSourceFactory\Model\Expression\ArrayExpression\ArrayExpression;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Statement\ReturnStatement;

class DataProviderMethodDefinition extends MethodDefinition implements DataProviderMethodDefinitionInterface
{
    use HasMetadataTrait;
    use IsNotStaticTrait;

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

        parent::__construct(
            $name,
            new Body(
                new BodyContentCollection()
                    ->append(
                        new ReturnStatement(
                            ArrayExpression::fromArray($data)
                        )
                    )
            ),
        );

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

    public function getReturnType(): TypeCollection
    {
        return TypeCollection::array();
    }

    public function getVisibility(): string
    {
        return 'public';
    }
}
