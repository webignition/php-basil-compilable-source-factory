<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\MethodInvocation;

use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\IsNotStaticTrait;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSourceFactory\Model\NeverEncapsulateWhenCastingTrait;
use webignition\BasilCompilableSourceFactory\Model\TypeCollection;

class ObjectConstructor implements InvocableInterface
{
    use IsNotStaticTrait;
    use NeverEncapsulateWhenCastingTrait;

    private MethodInvocation $invocation;

    private MetadataInterface $metadata;

    public function __construct(
        ClassName $class,
        MethodArgumentsInterface $arguments,
        bool $mightThrow,
    ) {
        $this->invocation = new MethodInvocation(
            $class->renderClassName(),
            $arguments,
            $mightThrow,
            TypeCollection::void()
        );

        $this->metadata = $this->invocation->getMetadata()->merge(
            new Metadata(classNames: [$class->getClassName()])
        );
    }

    public function getMetadata(): MetadataInterface
    {
        return $this->metadata;
    }

    public function getTemplate(): string
    {
        return 'new {{ method_invocation }}';
    }

    public function getContext(): array
    {
        return [
            'method_invocation' => $this->invocation,
        ];
    }

    public function mightThrow(): bool
    {
        return $this->invocation->mightThrow();
    }

    public function getType(): TypeCollection
    {
        return $this->invocation->getType();
    }
}
