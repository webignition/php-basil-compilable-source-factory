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
