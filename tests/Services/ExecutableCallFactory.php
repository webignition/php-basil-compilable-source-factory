<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasilCompilableSourceFactory\Handler\ClassDependencyHandler;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilationSource\Comment;
use webignition\BasilCompilationSource\EmptyLine;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilCompilationSource\MutableListLineListInterface;
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilCompilationSource\LineList;

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

        $lineList = new LineList();

        foreach ($classDependencies as $key => $value) {
            $lineList->addLinesFromSource($this->classDependencyHandler->createSource($value));
        }

        foreach ($setupStatements as $statement) {
            $lineList->addLine(new Statement($statement));
        }

        $lineList->addLinesFromSources($source->getSources());

        foreach ($teardownStatements as $statement) {
            $lineList->addLine(new Statement($statement));
        }

        $lines = [];

        foreach ($lineList->getLines() as $line) {
            if ($line instanceof EmptyLine) {
                $lines[] = '';
            }

            if ($line instanceof Comment) {
                $lines[] = '// ' . $line->getContent();
            }

            if ($line instanceof Statement) {
                $lines[] = $line->getContent() . ';';
            }
        }

        return $this->variablePlaceholderResolver->resolve(
            implode("\n", $lines),
            $variableIdentifiers
        );
    }

    public function createWithReturn(
        SourceInterface $source,
        array $variableIdentifiers = [],
        array $setupStatements = [],
        array $teardownStatements = [],
        ?MetadataInterface $additionalMetadata = null
    ): string {
        if ($source instanceof MutableListLineListInterface) {
            $source->mutateLastStatement(function (string $content) {
                return 'return ' . $content;
            });
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
