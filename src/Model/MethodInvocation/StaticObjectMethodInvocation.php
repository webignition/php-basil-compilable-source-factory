<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\MethodInvocation;

use webignition\BasilCompilableSourceFactory\Model\Metadata\MetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodArguments\MethodArgumentsInterface;
use webignition\BasilCompilableSourceFactory\Model\MethodInvocation\StaticObjectMethodInvocationInterface as SInterface;
use webignition\BasilCompilableSourceFactory\Model\StaticObject;

class StaticObjectMethodInvocation extends AbstractMethodInvocationEncapsulator implements SInterface
{
    private const RENDER_TEMPLATE = '{{ object }}::{{ method_invocation }}';

    private StaticObject $staticObject;

    private bool $isErrorSuppressed = false;

    public function __construct(
        StaticObject $staticObject,
        string $methodName,
        ?MethodArgumentsInterface $arguments = null
    ) {
        parent::__construct($methodName, $arguments);

        $this->staticObject = $staticObject;
    }

    public function getTemplate(): string
    {
        $template = self::RENDER_TEMPLATE;

        if ($this->isErrorSuppressed) {
            $template = self::ERROR_SUPPRESSION_PREFIX . $template;
        }

        return $template;
    }

    public function getContext(): array
    {
        return [
            'object' => $this->staticObject,
            'method_invocation' => $this->invocation,
        ];
    }

    public function getStaticObject(): StaticObject
    {
        return $this->staticObject;
    }

    public function setIsErrorSuppressed(bool $isErrorSuppressed): static
    {
        $new = clone $this;
        $new->isErrorSuppressed = $isErrorSuppressed;

        return $new;
    }

    protected function getAdditionalMetadata(): MetadataInterface
    {
        return $this->staticObject->getMetadata();
    }
}
