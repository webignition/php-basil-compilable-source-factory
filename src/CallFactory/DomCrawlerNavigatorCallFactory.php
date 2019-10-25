<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\StatementInterface;
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

    public function createFindCall(DomIdentifierInterface $identifier): StatementInterface
    {
        return $this->createElementCall($identifier, 'find');
    }

    public function createFindOneCall(DomIdentifierInterface $identifier): StatementInterface
    {
        return $this->createElementCall($identifier, 'findOne');
    }

    public function createHasCall(DomIdentifierInterface $identifier): StatementInterface
    {
        return $this->createElementCall($identifier, 'has');
    }

    public function createHasOneCall(DomIdentifierInterface $identifier): StatementInterface
    {
        return $this->createElementCall($identifier, 'hasOne');
    }

    private function createElementCall(DomIdentifierInterface $identifier, string $methodName): StatementInterface
    {
        $arguments = $this->createElementCallArguments($identifier);

        $variableDependencies = new VariablePlaceholderCollection();
        $domCrawlerNavigatorPlaceholder = $variableDependencies->create(VariableNames::DOM_CRAWLER_NAVIGATOR);

        $metadata = new Metadata();
        $metadata->add($arguments->getMetadata());
        $metadata->addVariableDependencies($variableDependencies);

        $statementContent = sprintf(
            (string) $domCrawlerNavigatorPlaceholder . '->' . $methodName . '(%s)',
            (string) $arguments
        );

        return new Statement($statementContent, $metadata);
    }

    private function createElementCallArguments(DomIdentifierInterface $elementIdentifier): StatementInterface
    {
        $elementConstructorStatement = $this->elementLocatorCallFactory->createConstructorCall($elementIdentifier);

        $parentIdentifier = $elementIdentifier->getParentIdentifier();
        if ($parentIdentifier instanceof DomIdentifierInterface) {
            $parentConstructorStatement = $this->elementLocatorCallFactory->createConstructorCall($parentIdentifier);

            $metadata = new Metadata();
            $metadata->add($elementConstructorStatement->getMetadata());
            $metadata->add($parentConstructorStatement->getMetadata());

            $statementContent = sprintf(
                '%s, %s',
                (string) $elementConstructorStatement,
                (string) $parentConstructorStatement
            );

            return new Statement($statementContent, $metadata);
        }

        return $elementConstructorStatement;
    }
}
