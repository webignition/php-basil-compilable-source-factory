<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilationSource\StatementList;
use webignition\BasilCompilationSource\StatementListInterface;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;

class VariableAssignmentFactory
{
    public static function createFactory(): VariableAssignmentFactory
    {
        return new VariableAssignmentFactory();
    }

    public function createForValueAccessor(
        StatementListInterface $accessor,
        VariablePlaceholder $placeholder,
        string $type = 'string',
        string $default = 'null'
    ): StatementListInterface {
        $assignment = clone $accessor;
        $assignment->prependStatement(-1, $placeholder . ' = ');
        $assignment->appendStatement(-1, ' ?? ' . $default);

        $variableExports = new VariablePlaceholderCollection([
            $placeholder,
        ]);

        $assignment = $assignment->withMetadata(
            $assignment->getMetadata()->withAdditionalVariableExports($variableExports)
        );

        return (new StatementList())
            ->withPredecessors([$assignment])
            ->withStatements([
                sprintf('%s = (%s) %s', (string) $placeholder, $type, (string) $placeholder)
            ]);
    }
}
