<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\VariablePlaceholderCollection;
use webignition\BasilModel\Identifier\DomIdentifierInterface;

class DomCrawlerNavigatorCallFactory
{
    private $elementLocatorCallFactory;

    public function __construct(ElementLocatorCallFactory $elementLocatorCallFactory)
    {
        $this->elementLocatorCallFactory = $elementLocatorCallFactory;
    }

    public static function createFactory(): DomCrawlerNavigatorCallFactory
    {
        return new DomCrawlerNavigatorCallFactory(
            ElementLocatorCallFactory::createFactory()
        );
    }

    public function createFindCall(DomIdentifierInterface $identifier): CodeBlockInterface
    {
        return $this->createElementCall($identifier, 'find');
    }

    public function createFindOneCall(DomIdentifierInterface $identifier): CodeBlockInterface
    {
        return $this->createElementCall($identifier, 'findOne');
    }

    public function createHasCall(DomIdentifierInterface $identifier): CodeBlockInterface
    {
        return $this->createElementCall($identifier, 'has');
    }

    public function createHasOneCall(DomIdentifierInterface $identifier): CodeBlockInterface
    {
        return $this->createElementCall($identifier, 'hasOne');
    }

    private function createElementCall(DomIdentifierInterface $identifier, string $methodName): CodeBlockInterface
    {
        $arguments = $this->createElementCallArguments($identifier);

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

    private function createElementCallArguments(DomIdentifierInterface $elementIdentifier): CodeBlockInterface
    {
        $elementConstructorBlock = $this->elementLocatorCallFactory->createConstructorCall($elementIdentifier);

        $parentIdentifier = $elementIdentifier->getParentIdentifier();
        if ($parentIdentifier instanceof DomIdentifierInterface) {
            $parentConstructorBlock = $this->elementLocatorCallFactory->createConstructorCall($parentIdentifier);

            $metadata = new Metadata();
            $metadata->add($elementConstructorBlock->getMetadata());
            $metadata->add($parentConstructorBlock->getMetadata());

            $elementConstructorStatement = $elementConstructorBlock->getLines()[0];
            $parentConstructorStatement = $parentConstructorBlock->getLines()[0];

            $statementContent = sprintf(
                '%s, %s',
                (string) $elementConstructorStatement,
                (string) $parentConstructorStatement
            );

            return new CodeBlock([
                new Statement($statementContent, $metadata),
            ]);
        }

        return $elementConstructorBlock;
    }
}
