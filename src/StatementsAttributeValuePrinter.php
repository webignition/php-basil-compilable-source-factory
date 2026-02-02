<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilModels\Model\Statement\StatementCollectionInterface;
use webignition\BasilModels\Model\Statement\StatementInterface;

readonly class StatementsAttributeValuePrinter
{
    public function __construct(
        private StatementSerializer $statementSerializer,
    ) {}

    public static function create(): self
    {
        return new StatementsAttributeValuePrinter(
            StatementSerializer::createFactory(),
        );
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

        foreach ($statements as $statement) {
            $renderedStatements[] = sprintf(
                $statementTemplate,
                $this->serializeStatement($statement),
            );
        }

        $renderedStatementsContent = implode("\n", $renderedStatements);

        return sprintf($statementsTemplate, trim($renderedStatementsContent));
    }

    private function serializeStatement(StatementInterface $statement): string
    {
        $content = $this->statementSerializer->serialize($statement);

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
