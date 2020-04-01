<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilableSource\Line\ExpressionInterface;
use webignition\BasilCompilableSource\Line\LiteralExpression;
use webignition\BasilCompilableSource\Line\MethodInvocation\StaticObjectMethodInvocation;
use webignition\BasilCompilableSource\StaticObject;
use webignition\BasilCompilableSourceFactory\SingleQuotedStringEscaper;
use webignition\DomElementIdentifier\ElementIdentifier;
use webignition\DomElementIdentifier\ElementIdentifierInterface;

class ElementIdentifierCallFactory
{
    private $singleQuotedStringEscaper;

    public function __construct(SingleQuotedStringEscaper $singleQuotedStringEscaper)
    {
        $this->singleQuotedStringEscaper = $singleQuotedStringEscaper;
    }

    public static function createFactory(): ElementIdentifierCallFactory
    {
        return new ElementIdentifierCallFactory(
            SingleQuotedStringEscaper::create()
        );
    }

    public function createConstructorCall(ElementIdentifierInterface $elementIdentifier): ExpressionInterface
    {
        $elementOnlyIdentifier = ElementIdentifier::fromAttributeIdentifier($elementIdentifier);
        $serializedSourceIdentifier = (string) json_encode($elementOnlyIdentifier, JSON_PRETTY_PRINT);

        return new StaticObjectMethodInvocation(
            new StaticObject(ElementIdentifier::class),
            'fromJson',
            [
                new LiteralExpression(
                    '\'' . $this->singleQuotedStringEscaper->escape($serializedSourceIdentifier) . '\''
                )
            ]
        );
    }
}
