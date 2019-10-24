<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilationSource\Statement;
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
    ): ?StatementListInterface {
        $assignment = clone $accessor;

        $assignment->prependLastStatement($placeholder . ' = ');
        $assignment->appendLastStatement(' ?? ' . $default);
        $assignment->addVariableExportsToLastStatement(new VariablePlaceholderCollection([
            $placeholder,
        ]));

        return new StatementList(array_merge(
            $assignment->getStatementObjects(),
            [
                new Statement(sprintf('%s = (%s) %s', (string) $placeholder, $type, (string) $placeholder)),
            ]
        ));
    }
}
