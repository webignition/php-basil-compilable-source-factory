<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\Line\StatementInterface;
use webignition\BasilModel\DataSet\DataSetCollectionInterface;
use webignition\BasilModel\DataSet\DataSetInterface;

class ArrayStatementFactory
{
    private const INDENT_SPACE_COUNT = 4;
    private const DEFAULT_INDENT_COUNT = 1;

    private $singleQuotedStringEscaper;

    public function __construct(SingleQuotedStringEscaper $singleQuotedStringEscaper)
    {
        $this->singleQuotedStringEscaper = $singleQuotedStringEscaper;
    }

    public static function createFactory(): ArrayStatementFactory
    {
        return new ArrayStatementFactory(
            SingleQuotedStringEscaper::create()
        );
    }

    public function create(DataSetCollectionInterface $dataSetCollection): StatementInterface
    {
        $dataSetsAsArrays = [];

        /* @var DataSetInterface $dataSet */
        foreach ($dataSetCollection as $dataSet) {
            $dataSetsAsArrays[(string) $dataSet->getName()] = $dataSet->getData();
        }

        $arrayAsString = $this->convertArrayToString($dataSetsAsArrays);

        return new Statement('return ' . rtrim($arrayAsString, ','));
    }

    private function convertArrayToString(array $array, int $indentCount = self::DEFAULT_INDENT_COUNT): string
    {
        if (empty($array)) {
            return '[]';
        }

        $containerIndentCount = min($indentCount, $indentCount - 1);
        $containerIndent = str_repeat(' ', $containerIndentCount * self::INDENT_SPACE_COUNT);

        $bodyIndent = str_repeat(' ', $indentCount * self::INDENT_SPACE_COUNT);

        $containerTemplate =
            '['. "\n"
            . '%s' . "\n"
            . $containerIndent . '],';

        $keyValueTemplate = $bodyIndent . "'%s' => %s";
        $keyValueStrings = [];

        foreach ($array as $key => $value) {
            $keyAsString = $this->singleQuotedStringEscaper->escape((string) $key);

            if (is_array($value)) {
                ksort($value);

                $valueAsString = $this->convertArrayToString($value, $indentCount + 1);
            } else {
                $valueAsString = "'" . $this->singleQuotedStringEscaper->escape((string) $value) . "',";
            }

            $keyValueStrings[] = sprintf($keyValueTemplate, $keyAsString, $valueAsString);
        }

        return sprintf($containerTemplate, implode("\n", $keyValueStrings));
    }
}
