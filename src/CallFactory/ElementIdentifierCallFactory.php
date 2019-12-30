<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilableSourceFactory\SingleQuotedStringEscaper;
use webignition\BasilCompilationSource\Block\ClassDependencyCollection;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\Line\ClassDependency;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\DomElementIdentifier\ElementIdentifier;
use webignition\DomElementIdentifier\ElementIdentifierInterface;

class ElementIdentifierCallFactory
{
    private const TEMPLATE = 'ElementIdentifier::fromJson(%s)';

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

    public function createConstructorCall(ElementIdentifierInterface $elementIdentifier): CodeBlockInterface
    {
        $serializedSourceIdentifier = (string) json_encode($elementIdentifier);

        $arguments = '\'' . $this->singleQuotedStringEscaper->escape($serializedSourceIdentifier) . '\'';

        $metadata = new Metadata();
        $metadata->addClassDependencies(new ClassDependencyCollection([
            new ClassDependency(ElementIdentifier::class),
        ]));

        return new CodeBlock([
            new Statement(sprintf(self::TEMPLATE, $arguments), $metadata)
        ]);
    }
}
