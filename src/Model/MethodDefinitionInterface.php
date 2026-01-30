<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model;

use webignition\BasilCompilableSourceFactory\Model\Attribute\AttributeInterface;
use webignition\Stubble\Resolvable\ResolvableInterface;

interface MethodDefinitionInterface extends HasMetadataInterface, ResolvableInterface, HasReturnTypeInterface
{
    /**
     * @return string[]
     */
    public function getArguments(): array;

    public function getName(): string;

    public function getVisibility(): string;

    public function isStatic(): bool;

    public function withAttribute(AttributeInterface $attribute): static;

    public function getReturnType(): TypeCollection;
}
