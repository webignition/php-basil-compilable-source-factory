<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilModel\Value\ObjectValueType;
use webignition\BasilModel\Value\ValueInterface;

class AccessorDefaultValueFactory
{
    private $singleQuotedStringEscaper;

    public function __construct(SingleQuotedStringEscaper $singleQuotedStringEscaper)
    {
        $this->singleQuotedStringEscaper = $singleQuotedStringEscaper;
    }

    public static function createFactory(): AccessorDefaultValueFactory
    {
        return new AccessorDefaultValueFactory(
            SingleQuotedStringEscaper::create()
        );
    }

    public function create(ValueInterface $value)
    {
        if ($value instanceof ObjectValueInterface && ObjectValueType::ENVIRONMENT_PARAMETER === $value->getType()) {
            $valueDefault = $value->getDefault();

            if ('' !== $valueDefault) {
                return ctype_digit($valueDefault)
                    ? (int) $valueDefault
                    : "'" . $this->singleQuotedStringEscaper->escape($valueDefault) . "'";
            }
        }

        return null;
    }
}
