<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Expression;

use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\NeverThrowsTrait;
use webignition\BasilCompilableSourceFactory\Model\TypeDeclaration\ObjectTypeDeclarationCollection;
use webignition\BasilCompilableSourceFactory\Model\VariableName;

class CatchExpression implements ExpressionInterface
{
    use NeverThrowsTrait;

    private const RENDER_TEMPLATE = '{{ class_list }} {{ variable }}';

    private ObjectTypeDeclarationCollection $classes;

    public function __construct(ObjectTypeDeclarationCollection $classes)
    {
        $this->classes = $classes;
    }

    public function getMetadata(): MetadataInterface
    {
        $metadata = new Metadata();

        return $metadata->merge($this->classes->getMetadata());
    }

    public function getTemplate(): string
    {
        return self::RENDER_TEMPLATE;
    }

    public function getContext(): array
    {
        return [
            'class_list' => $this->classes,
            'variable' => self::getVariableName(),
        ];
    }

    public static function getVariableName(): VariableName
    {
        return new VariableName('exception');
    }
}
