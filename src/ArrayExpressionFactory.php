<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSource\Expression\ArrayExpression;
use webignition\BasilModels\DataSet\DataSet;
use webignition\BasilModels\DataSet\DataSetCollectionInterface;

class ArrayExpressionFactory
{
    private SingleQuotedStringEscaper $singleQuotedStringEscaper;

    public function __construct(SingleQuotedStringEscaper $singleQuotedStringEscaper)
    {
        $this->singleQuotedStringEscaper = $singleQuotedStringEscaper;
    }

    public static function createFactory(): ArrayExpressionFactory
    {
        return new ArrayExpressionFactory(
            SingleQuotedStringEscaper::create()
        );
    }

    public function create(DataSetCollectionInterface $dataSetCollection): ArrayExpression
    {
        $dataSetsAsArrays = [];

        /* @var DataSet $dataSet */
        foreach ($dataSetCollection as $dataSet) {
            if ($dataSet instanceof DataSet) {
                $dataSetsAsArrays[(string) $dataSet->getName()] = $dataSet->getData();
            }
        }

        return new ArrayExpression($dataSetsAsArrays);
    }
}
