<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasilCompilableSourceFactory\Handler\ClassDependencyHandler;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilCompilationSource\StatementList;
use webignition\BasilCompilationSource\StatementListInterface;

class ExecutableCallFactory
{
    private $classDependencyHandler;
    private $variablePlaceholderResolver;

    public function __construct(
        HandlerInterface $classDependencyHandler,
        VariablePlaceholderResolver $variablePlaceholderResolver
    ) {
        $this->classDependencyHandler = $classDependencyHandler;
        $this->variablePlaceholderResolver = $variablePlaceholderResolver;
    }

    public static function createFactory(): ExecutableCallFactory
    {
        return new ExecutableCallFactory(
            ClassDependencyHandler::createHandler(),
            new VariablePlaceholderResolver()
        );
    }

    public function create(
        StatementListInterface $statementList,
        array $variableIdentifiers = [],
        array $setupStatements = [],
        array $teardownStatements = [],
        ?MetadataInterface $additionalMetadata = null
    ): string {
        if (null !== $additionalMetadata) {
            $metadata = $statementList->getMetadata();
            $metadata = $metadata->merge([
                $metadata,
                $additionalMetadata
            ]);

            $statementList = $statementList->withMetadata($metadata);
        }

        $metadata = $statementList->getMetadata();
        $classDependencies = $metadata->getClassDependencies();

        $executableCall = '';

        foreach ($classDependencies as $key => $value) {
            $executableCall .= (string) $this->classDependencyHandler->createSource($value) . ";\n";
        }

        foreach ($setupStatements as $statement) {
            $executableCall .= $statement . "\n";
        }

        $statements = $statementList->getStatements();

        array_walk($statements, function (string &$statement) {
            $statement .= ';';
        });

        $content = $this->variablePlaceholderResolver->resolve(
            implode("\n", $statements),
            $variableIdentifiers
        );

        $executableCall .= $content;

        foreach ($teardownStatements as $statement) {
            $executableCall .= "\n";
            $executableCall .= $statement;
        }

        return $executableCall;
    }

    public function createWithReturn(
        StatementListInterface $statementList,
        array $variableIdentifiers = [],
        array $setupStatements = [],
        array $teardownStatements = [],
        ?MetadataInterface $additionalMetadata = null
    ): string {
        $statements = $statementList->getStatements();
        $lastStatementPosition = count($statements) - 1;
        $lastStatement = $statements[$lastStatementPosition];
        $lastStatement = 'return ' . $lastStatement;
        $statements[$lastStatementPosition] = $lastStatement;

        $statementListWithReturn = (new StatementList())
            ->withStatements($statements)
            ->withMetadata($statementList->getMetadata());

        return $this->create(
            $statementListWithReturn,
            $variableIdentifiers,
            $setupStatements,
            $teardownStatements,
            $additionalMetadata
        );
    }
}
