<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

class QuotedStringExtractor
{
    public static function createExtractor(): QuotedStringExtractor
    {
        return new QuotedStringExtractor();
    }

    public function getQuotedValue(string $quotedString): string
    {
        if ('' === $quotedString) {
            return $quotedString;
        }

        if ('"' === $quotedString[0]) {
            $quotedString = mb_substr($quotedString, 1);
        }

        if ('"' === $quotedString[-1]) {
            $quotedString = mb_substr($quotedString, 0, -1);
        }

        return str_replace('\\"', '"', $quotedString);
    }
}
