<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\CallFactory;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\StatementList;
use webignition\BasilCompilationSource\StatementListInterface;
use webignition\BasilCompilationSource\Metadata;
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

    public function createFindCall(DomIdentifierInterface $identifier): StatementListInterface
    {
        return $this->createElementCall($identifier, 'find');
    }

    public function createFindOneCall(DomIdentifierInterface $identifier): StatementListInterface
    {
        return $this->createElementCall($identifier, 'findOne');
    }

    public function createHasCall(DomIdentifierInterface $identifier): StatementListInterface
    {
        return $this->createElementCall($identifier, 'has');
    }

    public function createHasOneCall(DomIdentifierInterface $identifier): StatementListInterface
    {
        return $this->createElementCall($identifier, 'hasOne');
    }

    private function createElementCall(
        DomIdentifierInterface $identifier,
        string $methodName
    ): StatementListInterface {
        $arguments = $this->createElementCallArguments($identifier);

        $variableDependencies = new VariablePlaceholderCollection();
        $domCrawlerNavigatorPlaceholder = $variableDependencies->create(VariableNames::DOM_CRAWLER_NAVIGATOR);

        $metadata = (new Metadata())
            ->merge([$arguments->getMetadata()])
            ->withAdditionalVariableDependencies($variableDependencies);

        $createStatement = sprintf(
            (string) $domCrawlerNavigatorPlaceholder . '->' . $methodName . '(%s)',
            (string) $arguments
        );

        return (new StatementList())
            ->withStatements([$createStatement])
            ->withMetadata($metadata);
    }

    private function createElementCallArguments(DomIdentifierInterface $elementIdentifier): StatementListInterface
    {
        $statementList = $this->elementLocatorCallFactory->createConstructorCall($elementIdentifier);

        $parentIdentifier = $elementIdentifier->getParentIdentifier();
        if ($parentIdentifier instanceof DomIdentifierInterface) {
            $parentElementLocatorConstructorCall = $this->elementLocatorCallFactory->createConstructorCall(
                $parentIdentifier
            );

            $metadata = (new Metadata())->merge([
                $statementList->getMetadata(),
                $parentElementLocatorConstructorCall->getMetadata(),
            ]);

            $statementList = (new StatementList())
                ->withStatements([
                    sprintf(
                        '%s, %s',
                        (string) $statementList,
                        (string) $parentElementLocatorConstructorCall
                    ),
                ])
                ->withMetadata($metadata);
        }

        return $statementList;
    }
}
