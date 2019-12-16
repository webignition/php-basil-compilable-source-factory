<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Exception;

abstract class AbstractUnsupportedSubjectException extends \Exception
{
    public const CODE_NONE = 0;
    public const CODE_UNKNOWN = 1;

    /**
     * @param mixed $subject
     * @param \Throwable|null $previous
     */
    public function __construct($subject, \Throwable $previous = null)
    {
        $code = self::CODE_NONE;

        if ($previous instanceof \Throwable) {
            $codes = $this->getCodes();
            $code = $codes[get_class($previous)] ?? self::CODE_UNKNOWN;
        }

        parent::__construct(
            $this->createMessage($subject),
            $code,
            $previous
        );
    }

    /**
     * @param mixed $subject
     *
     * @return string
     */
    abstract protected function createMessage($subject): string;

    /**
     * @return array<string, int>
     */
    abstract protected function getCodes(): array;
}
