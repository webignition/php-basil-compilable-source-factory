<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Annotation;

use webignition\BasilCompilableSourceFactory\Model\VariableName;

class ParameterAnnotation extends AbstractAnnotation implements AnnotationInterface
{
    public function __construct(string $type, VariableName $name)
    {
        parent::__construct('param', [$type, (string) $name]);
    }
}
