<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

class VariablePlaceholderResolver
{
    /**
     * @param array<string, string> $variableIdentifiers
     */
    public static function resolve(string $content, array $variableIdentifiers): string
    {
        $search = [];
        $replace = [];

        foreach ($variableIdentifiers as $identifier => $name) {
            $search[] = sprintf('{{ %s }}', $identifier);
            $replace[] = $name;
        }

        return (string) str_replace($search, $replace, $content);
    }
}
