<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\ModelFactory;

use webignition\BasilCompilableSourceFactory\Model\EnvironmentValue;
use webignition\QuotedStringValueExtractor\QuotedStringValueExtractor;

class EnvironmentValueFactory
{
    private const PREFIX = '$env.';
    private const WITH_DEFAULT_PATTERN = '/^[^|]+\|/';
    private const DEFAULT_DELIMITER = '|';

    private QuotedStringValueExtractor $quotedStringValueExtractor;

    public function __construct(QuotedStringValueExtractor $quotedStringValueExtractor)
    {
        $this->quotedStringValueExtractor = $quotedStringValueExtractor;
    }

    public static function createFactory(): EnvironmentValueFactory
    {
        return new EnvironmentValueFactory(
            QuotedStringValueExtractor::createExtractor()
        );
    }

    public function create(string $value): EnvironmentValue
    {
        $prefixLength = strlen(self::PREFIX);
        $propertyAndDefault = mb_substr($value, $prefixLength);

        $property = $propertyAndDefault;
        $default = null;

        if (preg_match(self::WITH_DEFAULT_PATTERN, $propertyAndDefault)) {
            $propertyNameDefaultParts = explode(self::DEFAULT_DELIMITER, $property, 2);

            $property = $propertyNameDefaultParts[0];
            $defaultPart = $propertyNameDefaultParts[1];

            if ('' !== $defaultPart) {
                $default = $this->quotedStringValueExtractor->getValue($defaultPart);
            }
        }

        return new EnvironmentValue($property, $default);
    }
}
