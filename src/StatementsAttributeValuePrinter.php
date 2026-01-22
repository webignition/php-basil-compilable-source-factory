<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilModels\Model\Statement\StatementCollectionInterface;
use webignition\BasilModels\Model\Statement\StatementInterface;

readonly class StatementsAttributeValuePrinter
{
    public static function create(): self
    {
        return new StatementsAttributeValuePrinter();
    }

    /**
     * @return non-empty-string
     */
    public function print(StatementCollectionInterface $statements): string
    {
        if (0 === count($statements)) {
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

        foreach ($statements as $index => $statement) {
            $serializedStatement = $this->serializeStatement($statement, $index);

            $renderedStatements[] = sprintf(
                $statementTemplate,
                addcslashes($serializedStatement, "'"),
            );
        }

        $renderedStatementsContent = implode("\n", $renderedStatements);

        return sprintf($statementsTemplate, trim($renderedStatementsContent));
    }

    private function serializeStatement(StatementInterface $statement, int $index): string
    {
        $data = $statement->jsonSerialize();
        $data['index'] = $index;

        $content = (string) json_encode($data, JSON_PRETTY_PRINT);
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
