<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilableSource\Expression\ExpressionInterface;
use webignition\BasilCompilableSource\Expression\LiteralExpression;
use webignition\BasilCompilableSource\MethodInvocation\StaticObjectMethodInvocation;
use webignition\BasilCompilableSource\StaticObject;
use webignition\BasilCompilableSourceFactory\SingleQuotedStringEscaper;
use webignition\DomElementIdentifier\ElementIdentifier;

class ElementIdentifierCallFactory
{
    private SingleQuotedStringEscaper $singleQuotedStringEscaper;

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

    public function createConstructorCall(string $serializedSourceIdentifier): ExpressionInterface
    {
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
