<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilationSource\Block\Block;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\MutableBlockInterface;
use webignition\BasilCompilationSource\SourceInterface;

use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;

class VariableAssignmentFactory
{
    public static function createFactory(): VariableAssignmentFactory
    {
        return new VariableAssignmentFactory();
    }

    public function createForValueAccessor(
        SourceInterface $accessor,
        VariablePlaceholder $placeholder,
        string $type = 'string',
        string $default = 'null'
    ): SourceInterface {
        $assignment = clone $accessor;

        if ($assignment instanceof MutableBlockInterface) {
            $assignment->mutateLastStatement(function (string $content) use ($placeholder, $default) {
                return $placeholder . ' = ' . $content . ' ?? ' . $default;
            });

            $assignment->addVariableExportsToLastStatement(new VariablePlaceholderCollection([
                $placeholder,
            ]));
        }

        return new Block([
            $assignment,
            new Statement(sprintf('%s = (%s) %s', (string) $placeholder, $type, (string) $placeholder))
        ]);
    }
}
