<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Exception;

use webignition\BasilModels\Assertion\AssertionInterface;

class UnsupportedAssertionException extends AbstractUnsupportedSubjectException
{
    public const CODE_UNSUPPORTED_IDENTIFIER = 2;
    public const CODE_UNSUPPORTED_VALUE = 3;

    private $assertion;

    public function __construct(AssertionInterface $assertion, \Throwable $previous = null)
    {
        parent::__construct(
            $assertion,
            $previous
        );

        $this->assertion = $assertion;
    }

    public function getAssertion(): object
    {
        return $this->assertion;
    }

    /**
     * @inheritDoc
     */
    protected function createMessage($subject): string
    {
        return $subject instanceof AssertionInterface ? 'Unsupported assertion "' . $subject->getSource() . '"' : '';
    }

    /**
     * @inheritDoc
     */
    protected function getCodes(): array
    {
        return [
            UnsupportedIdentifierException::class => self::CODE_UNSUPPORTED_IDENTIFIER,
            UnsupportedValueException::class => self::CODE_UNSUPPORTED_VALUE,
        ];
    }
}
