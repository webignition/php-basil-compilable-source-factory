<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\ModelFactory;

class IdentifierStringValueAndPositionExtractor
{
    private const POSITION_FIRST = 'first';
    private const POSITION_LAST = 'last';
    private const POSITION_PATTERN = ':(-?[0-9]+|first|last)';
    private const POSITION_REGEX = '/' . self::POSITION_PATTERN . '$/';

    /**
     * @param string $identifierString
     *
     * @return array<int, int|string|null>
     */
    public static function extract(string $identifierString): array
    {
        $positionMatches = [];

        preg_match(self::POSITION_REGEX, $identifierString, $positionMatches);

        $position = null;

        if (empty($positionMatches)) {
            $quotedValue = $identifierString;
        } else {
            $quotedValue = (string) preg_replace(self::POSITION_REGEX, '', $identifierString);

            $positionMatch = $positionMatches[0];
            $positionString = ltrim($positionMatch, ':');

            if (self::POSITION_FIRST === $positionString) {
                $position = 1;
            } elseif (self::POSITION_LAST === $positionString) {
                $position = -1;
            } else {
                $position = (int) $positionString;
            }
        }

        return [
            $quotedValue,
            $position,
        ];
    }
}
