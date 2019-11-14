<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Exception;

class UnsupportedModelException extends \Exception
{
    private $model;

    public function __construct(object $model)
    {
        parent::__construct('Unsupported model "' . get_class($model) . '"');

        $this->model = $model;
    }

    public function getModel(): object
    {
        return $this->model;
    }
}
