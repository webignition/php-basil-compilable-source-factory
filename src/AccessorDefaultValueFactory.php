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
        return $this->create($value, function (string $valueDefault) {
            return ctype_digit($valueDefault)
                ? (int) $valueDefault
                : null;
        });
    }

    public function createString(string $value): ?string
    {
        return $this->create($value, function (string $valueDefault) {
            return "'" . $this->singleQuotedStringEscaper->escape((string) $valueDefault) . "'";
        });
    }

    /**
     * @param string $value
     * @param callable $defaultValueHandler
     *
     * @return mixed|null
     */
    private function create(string $value, callable $defaultValueHandler)
    {
        if (EnvironmentValue::is($value)) {
            $environmentValue = $this->environmentValueFactory->create($value);
            $valueDefault = $environmentValue->getDefault();

            if (null !== $valueDefault) {
                return $defaultValueHandler($valueDefault);
            }
        }

        return null;
    }
}
