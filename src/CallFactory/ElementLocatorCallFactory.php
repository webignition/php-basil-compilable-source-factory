<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilableSourceFactory\PlaceholderFactory;
use webignition\BasilCompilableSourceFactory\SingleQuotedStringEscaper;
use webignition\BasilCompilationSource\Block\Block;
use webignition\BasilCompilationSource\Block\BlockInterface;
use webignition\BasilCompilationSource\Block\ClassDependencyCollection;
use webignition\BasilCompilationSource\Line\ClassDependency;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilModel\Identifier\DomIdentifierInterface;
use webignition\DomElementLocator\ElementLocator;

class ElementLocatorCallFactory
{
    const TEMPLATE = 'new ElementLocator(%s)';

    private $placeholderFactory;
    private $singleQuotedStringEscaper;

    public function __construct(
        PlaceholderFactory $placeholderFactory,
        SingleQuotedStringEscaper $singleQuotedStringEscaper
    ) {
        $this->placeholderFactory = $placeholderFactory;
        $this->singleQuotedStringEscaper = $singleQuotedStringEscaper;
    }

    public static function createFactory(): ElementLocatorCallFactory
    {
        return new ElementLocatorCallFactory(
            PlaceholderFactory::createFactory(),
            SingleQuotedStringEscaper::create()
        );
    }

    public function createConstructorCall(DomIdentifierInterface $elementIdentifier): BlockInterface
    {
        $elementLocator = $elementIdentifier->getLocator();

        $arguments = '\'' . $this->singleQuotedStringEscaper->escape($elementLocator) . '\'';

        $position = $elementIdentifier->getOrdinalPosition();
        if (null !== $position) {
            $arguments .= ', ' . $position;
        }

        $metadata = new Metadata();
        $metadata->addClassDependencies(new ClassDependencyCollection([
            new ClassDependency(ElementLocator::class),
        ]));

        return new Block([
            new Statement(sprintf(self::TEMPLATE, $arguments), $metadata)
        ]);
    }
}
