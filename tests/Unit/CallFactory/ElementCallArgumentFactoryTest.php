<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\CallFactory;

use webignition\BasilCompilableSourceFactory\CallFactory\ElementCallArgumentFactory;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Identifier\DomIdentifierInterface;
use webignition\DomElementLocator\ElementLocator;

class ElementCallArgumentFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ElementCallArgumentFactory
     */
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = ElementCallArgumentFactory::createFactory();
    }

    /**
     * @dataProvider createElementCallArgumentsDataProvider
     */
    public function testCreateElementCallArguments(
        DomIdentifierInterface $identifier,
        array $expectedStatements,
        MetadataInterface $expectedMetadata
    ) {
        $source  = $this->factory->createElementCallArguments($identifier);

        $this->assertEquals($expectedStatements, $source->getStatements());
        $this->assertEquals($expectedMetadata, $source->getMetadata());
    }

    public function createElementCallArgumentsDataProvider(): array
    {
        return [
            'no parent, no ordinal position' => [
                'identifier' => new DomIdentifier('.selector'),
                'expectedStatements' => [
                    "new ElementLocator('.selector')",
                ],
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ])),
            ],
            'no parent, has ordinal position' => [
                'identifier' => new DomIdentifier('.selector', 3),
                'expectedStatements' => [
                    "new ElementLocator('.selector', 3)",
                ],
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ])),
            ],
            'has parent, no ordinal position' => [
                'identifier' => (new DomIdentifier('.selector'))
                    ->withParentIdentifier(new DomIdentifier('.parent')),
                'expectedStatements' => [
                    "new ElementLocator('.selector'), new ElementLocator('.parent')",
                ],
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ])),
            ],
            'has parent, has ordinal position' => [
                'identifier' => (new DomIdentifier('.selector', 4))
                    ->withParentIdentifier(new DomIdentifier('.parent')),
                'expectedStatements' => [
                    "new ElementLocator('.selector', 4), new ElementLocator('.parent')",
                ],
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ])),
            ],
            'has parent, has ordinal positions' => [
                'identifier' => (new DomIdentifier('.selector', 4))
                    ->withParentIdentifier(new DomIdentifier('.parent', 2)),
                'expectedStatements' => [
                    "new ElementLocator('.selector', 4), new ElementLocator('.parent', 2)",
                ],
                'expectedMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(ElementLocator::class),
                    ])),
            ],
        ];
    }
}
