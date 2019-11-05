<?php declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasePantherTestCase\Options;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractGeneratedTestCase;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\Comment;
use webignition\BasilCompilationSource\EmptyLine;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilCompilationSource\LineListInterface;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\MethodDefinition;
use webignition\BasilCompilationSource\MethodDefinitionInterface;
use webignition\BasilCompilationSource\Statement;

class MethodDefinitionFactory
{
    public static function createSetUpBeforeClassMethodDefinition(string $fixture): MethodDefinitionInterface
    {
        $lineList = new LineList([
            new Comment('Test harness lines'),
            new Statement('parent::setUpBeforeClass()'),
            new Statement(
                'self::$client->request(\'GET\', Options::getBaseUri() . \'' . $fixture . '\')',
                (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(Options::class),
                        new ClassDependency(AbstractGeneratedTestCase::class),
                    ]))
            ),
        ]);

        $methodDefinition = new MethodDefinition('setUpBeforeClass', $lineList);
        $methodDefinition->setStatic();
        $methodDefinition->setReturnType('void');

        return $methodDefinition;
    }

    public static function createSetUpMethodDefinition(
        ?LineListInterface $additionalSetupStatements
    ): MethodDefinitionInterface {
        $lineList = new LineList([
            new Comment('Test harness lines'),
            new Statement('parent::setUp()'),
            new EmptyLine(),
            new Comment('Additional setup statements'),
            $additionalSetupStatements,
        ]);

        $methodDefinition = new MethodDefinition('setUp', $lineList);
        $methodDefinition->setProtected();
        $methodDefinition->setReturnType('void');

        return $methodDefinition;
    }
}
