<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\MutableBlockInterface;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;

class VariableAssignmentFactory
{
    public static function createFactory(): VariableAssignmentFactory
    {
        return new VariableAssignmentFactory();
    }

    public function createForValueAccessor(
        CodeBlockInterface $accessor,
        VariablePlaceholder $placeholder,
        string $type = 'string',
        string $default = 'null'
    ): CodeBlockInterface {
        $assignment = clone $accessor;

        if ($assignment instanceof MutableBlockInterface) {
            $assignment->mutateLastStatement(function (string $content) use ($placeholder, $default) {
                return $placeholder . ' = ' . $content . ' ?? ' . $default;
            });

            $assignment->addVariableExportsToLastStatement(new VariablePlaceholderCollection([
                $placeholder,
            ]));
        }

        return new CodeBlock([
            $assignment,
            new Statement(sprintf('%s = (%s) %s', (string) $placeholder, $type, (string) $placeholder))
        ]);
    }
}
