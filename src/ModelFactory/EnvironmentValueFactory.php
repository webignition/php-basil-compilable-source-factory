<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\ModelFactory;

use webignition\BasilCompilableSourceFactory\Model\EnvironmentValue;
use webignition\BasilCompilableSourceFactory\QuotedStringExtractor;

class EnvironmentValueFactory
{
    private const PREFIX = '$env.';
    private const WITH_DEFAULT_PATTERN = '/^[^|]+\|/';
    private const DEFAULT_DELIMITER = '|';

    private $quotedStringExtractor;

    public function __construct(QuotedStringExtractor $quotedValueExtractor)
    {
        $this->quotedStringExtractor = $quotedValueExtractor;
    }

    public static function createFactory(): EnvironmentValueFactory
    {
        return new EnvironmentValueFactory(
            new QuotedStringExtractor()
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
                $default = $this->quotedStringExtractor->getQuotedValue($defaultPart);
            }
        }

        return new EnvironmentValue($property, $default);
    }
}
