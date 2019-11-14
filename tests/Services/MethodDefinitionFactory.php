<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Services;

use webignition\BasePantherTestCase\Options;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractGeneratedTestCase;
use webignition\BasilCompilationSource\Block\ClassDependencyCollection;
use webignition\BasilCompilationSource\Block\CodeBlock;
use webignition\BasilCompilationSource\Block\CodeBlockInterface;
use webignition\BasilCompilationSource\Line\ClassDependency;
use webignition\BasilCompilationSource\Line\Comment;
use webignition\BasilCompilationSource\Line\EmptyLine;
use webignition\BasilCompilationSource\Line\Statement;
use webignition\BasilCompilationSource\Metadata\Metadata;
use webignition\BasilCompilationSource\MethodDefinition\MethodDefinition;
use webignition\BasilCompilationSource\MethodDefinition\MethodDefinitionInterface;

class MethodDefinitionFactory
{
    public static function createSetUpBeforeClassMethodDefinition(string $fixture): MethodDefinitionInterface
    {
        $block = new CodeBlock([
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

        $methodDefinition = new MethodDefinition('setUpBeforeClass', $block);
        $methodDefinition->setStatic();
        $methodDefinition->setReturnType('void');

        return $methodDefinition;
    }

    public static function createSetUpMethodDefinition(
        ?CodeBlockInterface $additionalSetupStatements
    ): MethodDefinitionInterface {
        $block = new CodeBlock([
            new Comment('Test harness lines'),
            new Statement('parent::setUp()'),
            new EmptyLine(),
            new Comment('Additional setup statements'),
            $additionalSetupStatements,
        ]);

        $methodDefinition = new MethodDefinition('setUp', $block);
        $methodDefinition->setProtected();
        $methodDefinition->setReturnType('void');

        return $methodDefinition;
    }
}
