<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

readonly class StatementsAttributeValuePrinter
{
    public function __construct(
        private SingleQuotedStringEscaper $singleQuotedStringEscaper,
    ) {}

    public static function create(): self
    {
        return new StatementsAttributeValuePrinter(
            SingleQuotedStringEscaper::create(),
        );
    }

    /**
     * @param array{'type':'action'|'assertion', 'statement':non-empty-string}[] $statementsData
     *
     * @return non-empty-string
     */
    public function print(array $statementsData): string
    {
        if ([] === $statementsData) {
            return '[]';
        }

        $statementsTemplate = <<< 'EOD'
        [
            %s
        ]
        EOD;

        $statementTemplate = <<< 'EOD'
            [
                'type' => '%s',
                'statement' => '%s',
            ],
        EOD;

        $renderedStatements = [];

        foreach ($statementsData as $statementData) {
            $renderedStatements[] = sprintf(
                $statementTemplate,
                $statementData['type'],
                $this->singleQuotedStringEscaper->escape($statementData['statement']),
            );
        }

        $renderedStatementsContent = implode("\n", $renderedStatements);

        return sprintf($statementsTemplate, trim($renderedStatementsContent));
    }
}
