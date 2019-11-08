<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasilCompilableSourceFactory\Handler\ClassDependencyHandler;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilationSource\ClassDefinitionInterface;
use webignition\BasilCompilationSource\Comment;
use webignition\BasilCompilationSource\LineInterface;
use webignition\BasilCompilationSource\MethodDefinitionInterface;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilCompilationSource\LineList;

class CodeGeneratorService
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

    public static function create(): CodeGeneratorService
    {
        return new CodeGeneratorService(
            ClassDependencyHandler::createHandler(),
            new VariablePlaceholderResolver()
        );
    }

    public function createForClassDefinition(
        ClassDefinitionInterface $classDefinition,
        string $baseClass = null,
        array $variableIdentifiers = []
    ) {
        $classDependencies = $classDefinition->getMetadata()->getClassDependencies();
        $useStatementLineList = new LineList();

        foreach ($classDependencies as $classDependency) {
            $useStatementLineList->addLinesFromSource($this->classDependencyHandler->handle($classDependency));
        }

        $useStatementLines = $this->createCodeLinesFromLineList($useStatementLineList);
        $useStatementCode = $this->resolveCodeLines($useStatementLines);

        $methodCode = [];

        foreach ($classDefinition->getMethods() as $methodDefinition) {
            $methodCode[] = $this->createForMethodDefinition($methodDefinition, $variableIdentifiers);
        }

        $extendsCode = null === $baseClass ? '' : 'extends ' . $baseClass;

        $classTemplate = <<<'EOD'
%s

class %s %s
{
%s
}
EOD;

        return sprintf(
            $classTemplate,
            $useStatementCode,
            $classDefinition->getName(),
            $extendsCode,
            implode("\n\n", $methodCode)
        );
    }

    private function createForMethodDefinition(
        MethodDefinitionInterface $methodDefinition,
        array $variableIdentifiers = []
    ): string {
        $methodTemplate = <<<'EOD'
    %s %s function %s() %s
    {
%s
    }
EOD;
        $lines = $this->createCodeLinesFromLineList(new LineList($methodDefinition->getSources()));
        array_walk($lines, function (string &$line) {
            $line = '        ' . trim($line);
        });

        $returnType = $methodDefinition->getReturnType();
        $returnTypeCode = null === $returnType
            ? ''
            : ': ' . $returnType;

        $linesCode = $this->resolveCodeLines($lines, $variableIdentifiers);

        return sprintf(
            $methodTemplate,
            $methodDefinition->getVisibility(),
            $methodDefinition->isStatic() ? 'static' : '',
            $methodDefinition->getName(),
            $returnTypeCode,
            $linesCode
        );
    }

    private function createCodeLinesFromLineList(LineList $lineList): array
    {
        $lines = [];

        foreach ($lineList->getLines() as $line) {
            $lines[] = $this->createCodeFromLineObject($line);
        }

        return $lines;
    }

    private function resolveCodeLines(array $lines, array $variableIdentifiers = []): string
    {
        return $this->variablePlaceholderResolver->resolve(
            implode("\n", $lines),
            $variableIdentifiers
        );
    }

    private function createCodeFromLineObject(LineInterface $line): string
    {
        if ($line instanceof Comment) {
            return '// ' . $line->getContent();
        }

        if ($line instanceof Statement) {
            return $line->getContent() . ';';
        }

        return '';
    }
}
