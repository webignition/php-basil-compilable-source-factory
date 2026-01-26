<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Annotation;

class ParameterAnnotation extends AbstractAnnotation implements AnnotationInterface
{
    public function __construct(string $type, string $name)
    {
        parent::__construct('param', [$type, $name]);
    }
}
