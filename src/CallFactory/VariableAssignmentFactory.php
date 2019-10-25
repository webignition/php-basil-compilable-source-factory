<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilCompilationSource\StatementList;
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

        $assignment->mutateLastStatement(function (string $content) use ($placeholder, $default) {
            return $placeholder . ' = ' . $content . ' ?? ' . $default;
        });

        $assignment->addVariableExportsToLastStatement(new VariablePlaceholderCollection([
            $placeholder,
        ]));

        $statementList = new StatementList([]);
        $statementList->addStatements($assignment->getStatementObjects());
        $statementList->addStatement(
            new Statement(sprintf('%s = (%s) %s', (string) $placeholder, $type, (string) $placeholder))
        );

        return $statementList;
    }
}
