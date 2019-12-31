<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\DomElementIdentifier\ElementIdentifierInterface;

class DomCrawlerNavigatorCallFactory
{
    private $elementIdentifierCallFactory;

    public function __construct(ElementIdentifierCallFactory $elementIdentifierCallFactory)
    {
        $this->elementIdentifierCallFactory = $elementIdentifierCallFactory;
    }

    public static function createFactory(): DomCrawlerNavigatorCallFactory
    {
        return new DomCrawlerNavigatorCallFactory(
            ElementIdentifierCallFactory::createFactory()
        );
    }

    public function createFindCall(ElementIdentifierInterface $identifier): CodeBlockInterface
    {
        return $this->createElementCall($identifier, 'find');
    }

    public function createFindOneCall(ElementIdentifierInterface $identifier): CodeBlockInterface
    {
        return $this->createElementCall($identifier, 'findOne');
    }

    public function createHasCall(ElementIdentifierInterface $identifier): CodeBlockInterface
    {
        return $this->createElementCall($identifier, 'has');
    }

    public function createHasOneCall(ElementIdentifierInterface $identifier): CodeBlockInterface
    {
        return $this->createElementCall($identifier, 'hasOne');
    }

    private function createElementCall(ElementIdentifierInterface $identifier, string $methodName): CodeBlockInterface
    {
        $arguments = $this->elementIdentifierCallFactory->createConstructorCall($identifier);

        $variableDependencies = new VariablePlaceholderCollection();
        $domCrawlerNavigatorPlaceholder = $variableDependencies->create(VariableNames::DOM_CRAWLER_NAVIGATOR);

        $metadata = new Metadata();
        $metadata->add($arguments->getMetadata());
        $metadata->addVariableDependencies($variableDependencies);

        $argumentsStatement = $arguments->getLines()[0];

        $statementContent = sprintf(
            (string) $domCrawlerNavigatorPlaceholder . '->' . $methodName . '(%s)',
            (string) $argumentsStatement
        );

        return new CodeBlock([
            new Statement($statementContent, $metadata),
        ]);
    }
}
