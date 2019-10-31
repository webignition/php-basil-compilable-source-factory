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
use webignition\BasilCompilationSource\SourceInterface;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilCompilationSource\LineList;

class CodeGenerator
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

    public static function create(): CodeGenerator
    {
        return new CodeGenerator(
            ClassDependencyHandler::createHandler(),
            new VariablePlaceholderResolver()
        );
    }

    public function createForLines(
        SourceInterface $source,
        array $variableIdentifiers = [],
        ?LineList $setupStatements = null,
        ?LineList $teardownStatements = null,
        ?MetadataInterface $additionalMetadata = null
    ): string {
        $setupStatements = $setupStatements ?? new LineList();
        $teardownStatements = $teardownStatements ?? new LineList();
        $additionalMetadata = $additionalMetadata ?? new Metadata();

        $metadata = new Metadata();
        $metadata->add($source->getMetadata());
        $metadata->add($additionalMetadata);

        $classDependencies = $metadata->getClassDependencies();

        $lineList = new LineList();

        foreach ($classDependencies as $key => $value) {
            $lineList->addLinesFromSource($this->classDependencyHandler->createSource($value));
        }

        $lineList->addLinesFromSource($setupStatements);
        $lineList->addLinesFromSources($source->getSources());
        $lineList->addLinesFromSource($teardownStatements);

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
}
