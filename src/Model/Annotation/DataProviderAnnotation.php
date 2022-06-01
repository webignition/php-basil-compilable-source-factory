<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Annotation;

class DataProviderAnnotation extends AbstractAnnotation implements AnnotationInterface
{
    public function __construct(string $dataProviderMethodName)
    {
        parent::__construct('dataProvider', [$dataProviderMethodName]);
    }
}
