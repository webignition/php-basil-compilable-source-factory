<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;

class VariableAssignmentFactory
{
    public static function createFactory(): VariableAssignmentFactory
    {
        return new VariableAssignmentFactory();
    }

    /**
     * @param CodeBlockInterface $accessor
     * @param VariablePlaceholder $placeholder
     * @param int|string|null $default
     *
     * @return CodeBlockInterface
     */
    public function createForValueAccessor(
        CodeBlockInterface $accessor,
        VariablePlaceholder $placeholder,
        $default = null
    ): CodeBlockInterface {
        $type = is_int($default) ? 'int' : 'string';
        $default = null === $default ? 'null' : (string) $default;

        $assignment = clone $accessor;
        $assignment->mutateLastStatement(function (string $content) use ($placeholder, $default) {
            return $placeholder . ' = ' . $content . ' ?? ' . $default;
        });

        $assignment->addVariableExportsToLastStatement(new VariablePlaceholderCollection([
            $placeholder,
        ]));

        return new CodeBlock([
            $assignment,
            new Statement(sprintf('%s = (%s) %s', (string) $placeholder, $type, (string) $placeholder))
        ]);
    }
}
