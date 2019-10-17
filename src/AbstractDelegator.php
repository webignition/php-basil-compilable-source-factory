<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

abstract class AbstractDelegator implements DelegatorInterface
{
    /**
     * @var HandlerInterface[]
     */
    private $handlers = [];

    public function __construct(array $handlers = [])
    {
        foreach ($handlers as $handler) {
            if ($this->isAllowedHandler($handler)) {
                $this->handlers[] = $handler;
            }
        }
    }

    public function findHandler(object $model): ?HandlerInterface
    {
        foreach ($this->handlers as $transpiler) {
            if ($transpiler->handles($model)) {
                return $transpiler;
            }
        }

        return null;
    }
}
