<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilModels\Model\StatementInterface;

readonly class StatementsAttributeValuePrinter
{
    public static function create(): self
    {
        return new StatementsAttributeValuePrinter();
    }

    /**
     * @param StatementInterface[] $statements
     *
     * @return non-empty-string
     */
    public function print(array $statements): string
    {
        if ([] === $statements) {
            return '[]';
        }

        $statementsTemplate = <<< 'EOD'
        [
            %s
        ]
        EOD;

        $statementTemplate = <<< 'EOD'
            '%s',
        EOD;

        $renderedStatements = [];

        foreach ($statements as $statement) {
            $serializedStatement = $this->serializeStatement($statement);

            $renderedStatements[] = sprintf(
                $statementTemplate,
                addcslashes($serializedStatement, "'"),
            );
        }

        $renderedStatementsContent = implode("\n", $renderedStatements);

        return sprintf($statementsTemplate, trim($renderedStatementsContent));
    }

    private function serializeStatement(StatementInterface $statement): string
    {
        $content = (string) json_encode($statement, JSON_PRETTY_PRINT);
        $lines = explode("\n", $content);

        foreach ($lines as $index => $line) {
            if (0 === $index) {
                $lines[$index] = $line;
            } else {
                $lines[$index] = '    ' . $line;
            }
        }

        return implode("\n", $lines);
    }
}
