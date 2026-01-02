<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilModels\Model\Action\ActionInterface;

readonly class IndexedAction extends IndexedStatement implements ActionInterface
{
    public function __construct(
        private ActionInterface $action,
        int $index,
    ) {
        parent::__construct($action, $index);
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return array_merge(
            parent::jsonSerialize(),
            $this->action->jsonSerialize(),
        );
    }

    public function getType(): string
    {
        return $this->action->getType();
    }

    public function getArguments(): ?string
    {
        return $this->action->getArguments();
    }

    public function isBrowserOperation(): bool
    {
        return $this->action->isBrowserOperation();
    }

    public function isInteraction(): bool
    {
        return $this->action->isInteraction();
    }

    public function isInput(): bool
    {
        return $this->action->isInput();
    }

    public function isWait(): bool
    {
        return $this->action->isWait();
    }
}
