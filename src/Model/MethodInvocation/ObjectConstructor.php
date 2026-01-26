<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\MethodInvocation;

use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;

class ObjectConstructor extends AbstractMethodInvocationEncapsulator
{
    private const RENDER_TEMPLATE = 'new {{ method_invocation }}';

    private ClassName $class;

    public function __construct(
        ClassName $class,
        MethodArgumentsInterface $arguments,
        bool $mightThrow
    ) {
        parent::__construct($class->renderClassName(), $arguments, $mightThrow);

        $this->class = $class;
    }

    public function getTemplate(): string
    {
        return self::RENDER_TEMPLATE;
    }

    public function getContext(): array
    {
        return [
            'method_invocation' => $this->invocation,
        ];
    }

    protected function getAdditionalMetadata(): MetadataInterface
    {
        return new Metadata(classNames: [$this->class->getClassName()]);
    }
}
