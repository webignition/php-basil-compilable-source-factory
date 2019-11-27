<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

class IdentifierTypeFinder
{
    private const POSITION_PATTERN = ':(-?[0-9]+|first|last)';
    private const ELEMENT_IDENTIFIER_STARTING_PATTERN = '^\$"';
    private const ELEMENT_IDENTIFIER_ENDING_PATTERN = '("|' . self::POSITION_PATTERN . ')';
    private const CSS_SELECTOR_STARTING_PATTERN = '((?!\/).).+';
    private const XPATH_EXPRESSION_STARTING_PATTERN = '\/.+';

    private const CSS_SELECTOR_REGEX =
        '/' . self::ELEMENT_IDENTIFIER_STARTING_PATTERN .
        self::CSS_SELECTOR_STARTING_PATTERN .
        self::ELEMENT_IDENTIFIER_ENDING_PATTERN .
        '$/';

    private const XPATH_EXPRESSION_REGEX =
        '/' . self::ELEMENT_IDENTIFIER_STARTING_PATTERN .
        self::XPATH_EXPRESSION_STARTING_PATTERN .
        self::ELEMENT_IDENTIFIER_ENDING_PATTERN .
        '$/';

    private const ATTRIBUTE_IDENTIFIER_REGEX =
        '/' . self::ELEMENT_IDENTIFIER_STARTING_PATTERN .
        '(' . self::CSS_SELECTOR_STARTING_PATTERN . ')|(' . self::XPATH_EXPRESSION_STARTING_PATTERN . ')' .
        self::ELEMENT_IDENTIFIER_ENDING_PATTERN .
        '\.(.+)' .
        '$/';

    public static function isCssSelector(string $identifier): bool
    {
        return 1 === preg_match(self::CSS_SELECTOR_REGEX, $identifier);
    }

    public static function isXpathExpression(string $identifier): bool
    {
        return 1 === preg_match(self::XPATH_EXPRESSION_REGEX, $identifier);
    }

    public static function isElementIdentifier(string $identifier): bool
    {
        return self::isCssSelector($identifier) || self::isXpathExpression($identifier);
    }

    public static function isAttributeIdentifier(string $identifier): bool
    {
        if (self::isElementIdentifier($identifier)) {
            return false;
        }

        return 1 === preg_match(self::ATTRIBUTE_IDENTIFIER_REGEX, $identifier);
    }

    public static function isDomIdentifier(string $identifier): bool
    {
        return self::isElementIdentifier($identifier) || self::isAttributeIdentifier($identifier);
    }
}
