<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasilCompilationSource\VariablePlaceholder;

class VariablePlaceholderResolver
{
    public function resolve(string $content, array $variableIdentifiers): string
    {
        $search = [];
        $replace = [];

        foreach ($variableIdentifiers as $identifier => $name) {
            $search[] = sprintf(VariablePlaceholder::TEMPLATE, $identifier);
            $replace[] = $name;
        }

        return (string) str_replace($search, $replace, $content);
    }
}
