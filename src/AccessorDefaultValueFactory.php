<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\Model\EnvironmentValue;
use webignition\BasilCompilableSourceFactory\ModelFactory\EnvironmentValueFactory;

class AccessorDefaultValueFactory
{
    private $singleQuotedStringEscaper;
    private $environmentValueFactory;

    public function __construct(
        SingleQuotedStringEscaper $singleQuotedStringEscaper,
        EnvironmentValueFactory $environmentValueFactory
    ) {
        $this->singleQuotedStringEscaper = $singleQuotedStringEscaper;
        $this->environmentValueFactory = $environmentValueFactory;
    }

    public static function createFactory(): AccessorDefaultValueFactory
    {
        return new AccessorDefaultValueFactory(
            SingleQuotedStringEscaper::create(),
            EnvironmentValueFactory::createFactory()
        );
    }

    public function createInteger(string $value): ?int
    {
        if (EnvironmentValue::is($value)) {
            $environmentValue = $this->environmentValueFactory->create($value);
            $valueDefault = $environmentValue->getDefault();

            if (null !== $valueDefault) {
                return ctype_digit($valueDefault)
                    ? (int) $valueDefault
                    : null;
            }
        }

        return null;
    }

    public function createString(string $value): ?string
    {
        if (EnvironmentValue::is($value)) {
            $environmentValue = $this->environmentValueFactory->create($value);
            $valueDefault = $environmentValue->getDefault();

            if (null !== $valueDefault) {
                $valueDefault = (string) $valueDefault;

                return "'" . $this->singleQuotedStringEscaper->escape($valueDefault) . "'";
            }
        }

        return null;
    }

    public function create(string $value)
    {
        if (EnvironmentValue::is($value)) {
            $environmentValue = $this->environmentValueFactory->create($value);
            $valueDefault = $environmentValue->getDefault();

            if (null !== $valueDefault) {
                return ctype_digit($valueDefault)
                    ? (int) $valueDefault
                    : "'" . $this->singleQuotedStringEscaper->escape($valueDefault) . "'";
            }
        }

        return null;
    }
}
