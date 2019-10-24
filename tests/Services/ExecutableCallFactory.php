<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasilCompilableSourceFactory\Handler\ClassDependencyHandler;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\StatementInterface;
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
        SourceInterface $source,
        array $variableIdentifiers = [],
        array $setupStatements = [],
        array $teardownStatements = [],
        ?MetadataInterface $additionalMetadata = null
    ): string {
        $additionalMetadata = $additionalMetadata ?? new Metadata();

        $metadata = new Metadata();
        $metadata->add($source->getMetadata());
        $metadata->add($additionalMetadata);

        $classDependencies = $metadata->getClassDependencies();

        $executableCall = '';

        foreach ($classDependencies as $key => $value) {
            $classDependencyStatementList = $this->classDependencyHandler->createStatementList($value);

            foreach ($classDependencyStatementList->getStatements() as $classDependencyStatement) {
                $executableCall .= $classDependencyStatement . ";\n";
            }
        }

        foreach ($setupStatements as $statement) {
            $executableCall .= $statement . "\n";
        }

        $statements = $source->getStatements();

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
        SourceInterface $source,
        array $variableIdentifiers = [],
        array $setupStatements = [],
        array $teardownStatements = [],
        ?MetadataInterface $additionalMetadata = null
    ): string {
        if ($source instanceof StatementInterface) {
            $source = new StatementList([$source]);
        }

        if ($source instanceof StatementListInterface) {
            $source->prependLastStatement('return ');
        }

        return $this->create(
            $source,
            $variableIdentifiers,
            $setupStatements,
            $teardownStatements,
            $additionalMetadata
        );
    }
}
