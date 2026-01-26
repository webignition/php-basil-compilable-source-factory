<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Expression;

use webignition\BasilCompilableSourceFactory\Model\IsAssigneeInterface;
use webignition\BasilCompilableSourceFactory\Model\IsNotStaticTrait;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;

class AssignmentExpression implements AssignmentExpressionInterface
{
    use IsNotStaticTrait;

    public const OPERATOR_ASSIGMENT_EQUALS = '=';
    private const RENDER_TEMPLATE = '{{ assignee }} {{ operator }} {{ value }}';

    private IsAssigneeInterface $assignee;
    private ExpressionInterface $value;
    private string $operator;

    public function __construct(
        IsAssigneeInterface $assignee,
        ExpressionInterface $value,
        string $operator = self::OPERATOR_ASSIGMENT_EQUALS
    ) {
        $this->assignee = $assignee;
        $this->value = $value;
        $this->operator = $operator;
    }

    public function getAssignee(): IsAssigneeInterface
    {
        return $this->assignee;
    }

    public function getValue(): ExpressionInterface
    {
        return $this->value;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata()
            ->merge($this->assignee->getMetadata())
            ->merge($this->value->getMetadata())
        ;
    }

    public function getTemplate(): string
    {
        return self::RENDER_TEMPLATE;
    }

    public function getContext(): array
    {
        return [
            'assignee' => $this->assignee,
            'operator' => $this->operator,
            'value' => $this->value,
        ];
    }

    public function mightThrow(): bool
    {
        return $this->assignee->mightThrow() || $this->value->mightThrow();
    }
}
