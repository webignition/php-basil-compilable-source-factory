<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasilCompilableSourceFactory\Handler\ClassDependencyHandler;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilationSource\ClassDefinition;
use webignition\BasilCompilationSource\ClassDefinitionInterface;
use webignition\BasilCompilationSource\Comment;
use webignition\BasilCompilationSource\EmptyLine;
use webignition\BasilCompilationSource\LineInterface;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilCompilationSource\MethodDefinition;
use webignition\BasilCompilationSource\MethodDefinitionInterface;
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

    public function createForClassDefinition(
        ClassDefinitionInterface $classDefinition,
        string $baseClass = null,
        array $variableIdentifiers = []
    ) {
        $classDependencies = $classDefinition->getMetadata()->getClassDependencies();
        $useStatementLineList = new LineList();

        foreach ($classDependencies as $classDependency) {
            $useStatementLineList->addLinesFromSource($this->classDependencyHandler->createSource($classDependency));
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
public function %s() %s
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
            $methodDefinition->getName(),
            $returnTypeCode,
            $linesCode
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

        $lines = $this->createCodeLinesFromLineList($lineList);

        return $this->resolveCodeLines($lines, $variableIdentifiers);
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

    public function wrapLineListInPhpUnitTestClass(
        SourceInterface $source,
        callable $initializer,
        array $variableIdentifiers,
        ?string $methodReturnType = null
    ): string {
        $methodDefinition = $this->createLineListWrapper(
            new LineList([$source]),
            $methodReturnType
        );

        $classDefinition = $this->createMethodListWrapper([$methodDefinition]);
        $classDefinitionCode = $this->createForClassDefinition(
            $classDefinition,
            '\PHPUnit\Framework\TestCase',
            $variableIdentifiers
        );

        $initSource = $initializer($classDefinition, $methodDefinition);
        $initCode = $this->createForLines($initSource, $variableIdentifiers);

        return $classDefinitionCode . "\n\n" . $initCode;
    }

    private function createMethodListWrapper(array $methods)
    {
        $className = 'GeneratedClass' . ucfirst(md5((string) rand()));

        return new ClassDefinition($className, $methods);
    }

    private function createLineListWrapper(LineList $lineList, ?string $returnType = null): MethodDefinitionInterface
    {
        $methodName = 'generatedMethod' . ucfirst(md5((string) rand()));

        $methodDefinition = new MethodDefinition(
            $methodName,
            $lineList
        );

        if (null !== $returnType) {
            $methodDefinition->setReturnType($returnType);
        }

        return $methodDefinition;
    }

    public function createLineListWrapperInitializer(): callable
    {
        return function (
            ClassDefinitionInterface $classDefinition,
            MethodDefinitionInterface $methodDefinition
        ): LineList {
            return new LineList([
                new Statement('(new ' . $classDefinition->getName() . '())->' . $methodDefinition->getName() . '()'),
            ]);
        };
    }

    public function createLineListWrapperReturningInitializer(): callable
    {
        return function (
            ClassDefinitionInterface $classDefinition,
            MethodDefinitionInterface $methodDefinition
        ): LineList {
            return new LineList([
                new Statement('return (new ' . $classDefinition->getName() . '())->' . $methodDefinition->getName() . '()'),
            ]);
        };
    }
}
