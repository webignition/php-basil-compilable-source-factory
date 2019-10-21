<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilableSourceFactory\PlaceholderFactory;
use webignition\BasilCompilableSourceFactory\SingleQuotedStringEscaper;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\Source;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\Metadata;
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

    /**
     * @param DomIdentifierInterface $elementIdentifier
     *
     * @return SourceInterface
     */
    public function createConstructorCall(DomIdentifierInterface $elementIdentifier): SourceInterface
    {
        $elementLocator = $elementIdentifier->getLocator();

        $arguments = '\'' . $this->singleQuotedStringEscaper->escape($elementLocator) . '\'';

        $position = $elementIdentifier->getOrdinalPosition();
        if (null !== $position) {
            $arguments .= ', ' . $position;
        }

        $statement = sprintf(self::TEMPLATE, $arguments);

        $metadata = (new Metadata())->withClassDependencies(new ClassDependencyCollection([
            new ClassDependency(ElementLocator::class),
        ]));

        return (new Source())
            ->withStatements([$statement])
            ->withMetadata($metadata);
    }
}
