<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Transpiler\ClassDependencyHandler;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilCompilationSource\Source;
use webignition\BasilCompilationSource\SourceInterface;

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
        if (null !== $additionalMetadata) {
            $metadata = $source->getMetadata();
            $metadata = $metadata->merge([
                $metadata,
                $additionalMetadata
            ]);

            $source = $source->withMetadata($metadata);
        }

        $metadata = $source->getMetadata();
        $classDependencies = $metadata->getClassDependencies();

        $executableCall = '';

        foreach ($classDependencies as $key => $value) {
            $executableCall .= (string) $this->classDependencyHandler->createSource($value) . ";\n";
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
        $statements = $source->getStatements();
        $lastStatementPosition = count($statements) - 1;
        $lastStatement = $statements[$lastStatementPosition];
        $lastStatement = 'return ' . $lastStatement;
        $statements[$lastStatementPosition] = $lastStatement;

        $sourceWithReturn = (new Source())
            ->withStatements($statements)
            ->withMetadata($source->getMetadata());

        return $this->create(
            $sourceWithReturn,
            $variableIdentifiers,
            $setupStatements,
            $teardownStatements,
            $additionalMetadata
        );
    }
}
