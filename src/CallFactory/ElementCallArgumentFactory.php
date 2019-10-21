<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilationSource\Source;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilModel\Identifier\DomIdentifierInterface;

class ElementCallArgumentFactory
{
    private $elementLocatorCallFactory;

    public function __construct(ElementLocatorCallFactory $elementLocatorCallFactory)
    {
        $this->elementLocatorCallFactory = $elementLocatorCallFactory;
    }

    public static function createFactory(): ElementCallArgumentFactory
    {
        return new ElementCallArgumentFactory(
            ElementLocatorCallFactory::createFactory()
        );
    }

    public function createElementCallArguments(DomIdentifierInterface $identifier): SourceInterface
    {
        $source = $this->elementLocatorCallFactory->createConstructorCall($identifier);

        $parentIdentifier = $identifier->getParentIdentifier();
        if ($parentIdentifier instanceof DomIdentifierInterface) {
            $parentElementLocatorConstructorCall = $this->elementLocatorCallFactory->createConstructorCall(
                $parentIdentifier
            );

            $metadata = (new Metadata())->merge([
                $source->getMetadata(),
                $parentElementLocatorConstructorCall->getMetadata(),
            ]);

            $source = (new Source())
                ->withStatements([
                    sprintf(
                        '%s, %s',
                        (string) $source,
                        (string) $parentElementLocatorConstructorCall
                    ),
                ])
                ->withMetadata($metadata);
        }

        return $source;
    }
}
