<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Transpiler;

use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifier;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierInterface;
use webignition\BasilCompilableSourceFactory\Model\NamedDomIdentifierValue;
use webignition\BasilCompilableSourceFactory\Transpiler\NamedDomIdentifierTranspiler;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilCompilationSource\VariablePlaceholder;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;
use webignition\WebDriverElementCollection\WebDriverElementCollection;
use webignition\WebDriverElementInspector\Inspector;

class NamedDomIdentifierTranspilerTest extends AbstractTranspilerTest
{
    protected function createTranspiler(): HandlerInterface
    {
        return NamedDomIdentifierTranspiler::createHandler();
    }
    /**
     * @dataProvider transpileDataProvider
     */
    public function testTranspile(
        string $fixture,
        NamedDomIdentifierInterface $namedDomIdentifier,
        callable $resultAssertions,
        array $additionalSetupStatements = [],
        array $additionalVariableIdentifiers = [],
        ?MetadataInterface $additionalMetadata = null
    ) {
        $source = $this->transpiler->createSource($namedDomIdentifier);

        $setupStatements = array_merge(
            [
                '$navigator = Navigator::create($crawler);',
            ],
            $additionalSetupStatements
        );

        $variableIdentifiers = array_merge(
            $additionalVariableIdentifiers,
            [
                'HAS' => '$has',
                'ELEMENT' => '$element',
                VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                VariableNames::PHPUNIT_TEST_CASE => self::PHPUNIT_TEST_CASE_VARIABLE_NAME,
            ]
        );

        $metadata = $additionalMetadata ?? new Metadata();
        $metadata = $this->addNavigatorToMetadata($metadata);

        $executableCall = $this->createExecutableCallForRequestWithReturn(
            $fixture,
            $source,
            $setupStatements,
            $variableIdentifiers,
            $metadata
        );

        $resultAssertions(eval($executableCall));
    }

    public function transpileDataProvider(): array
    {
        return [
            'element value, no parent' => [
                'fixture' => '/form.html',
                'model' => new NamedDomIdentifierValue(
                    new DomIdentifierValue(
                        new DomIdentifier('input', 1)
                    ),
                    new VariablePlaceholder('ELEMENT')
                ),
                'resultAssertions' => function ($result) {
                    $this->assertEquals('', $result);
                },
                'additionalSetupStatements' => [
                    '$inspector = Inspector::create();',
                ],
                'additionalVariableIdentifiers' => [
                    VariableNames::WEBDRIVER_ELEMENT_INSPECTOR => self::WEBDRIVER_ELEMENT_INSPECTOR_VARIABLE_NAME,
                ],
                'additionalMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(Inspector::class),
                    ]))
            ],
            'element value, has parent' => [
                'fixture' => '/form.html',
                'model' => new NamedDomIdentifierValue(
                    new DomIdentifierValue(
                        (new DomIdentifier('input', 1))
                            ->withParentIdentifier(new DomIdentifier('form[action="/action2"]'))
                    ),
                    new VariablePlaceholder('ELEMENT')
                ),
                'resultAssertions' => function ($result) {
                    $this->assertEquals('test', $result);
                },
                'additionalSetupStatements' => [
                    '$inspector = Inspector::create();',
                ],
                'additionalVariableIdentifiers' => [
                    VariableNames::WEBDRIVER_ELEMENT_INSPECTOR => self::WEBDRIVER_ELEMENT_INSPECTOR_VARIABLE_NAME,
                ],
                'additionalMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(Inspector::class),
                    ]))
            ],
            'attribute value, no parent' => [
                'fixture' => '/form.html',
                'model' => new NamedDomIdentifierValue(
                    new DomIdentifierValue(
                        (new DomIdentifier('input', 1))->withAttributeName('name')
                    ),
                    new VariablePlaceholder('ELEMENT')
                ),
                'resultAssertions' => function ($result) {
                    $this->assertEquals('input-without-value', $result);
                },
            ],
            'attribute value, has parent' => [
                'fixture' => '/form.html',
                'model' => new NamedDomIdentifierValue(
                    new DomIdentifierValue(
                        (new DomIdentifier('input', 1))
                            ->withAttributeName('name')
                            ->withParentIdentifier(new DomIdentifier('form[action="/action2"]'))
                    ),
                    new VariablePlaceholder('ELEMENT')
                ),
                'resultAssertions' => function ($result) {
                    $this->assertEquals('input-2', $result);
                },
            ],
            'element identifier, no parent' => [
                'fixture' => '/form.html',
                'model' => new NamedDomIdentifier(
                    new DomIdentifier('input', 1),
                    new VariablePlaceholder('ELEMENT')
                ),
                'resultAssertions' => function (WebDriverElementCollection $collection) {
                    $this->assertCount(1, $collection);

                    $element = $collection->current();
                    $this->assertEquals('', $element->getAttribute('value'));
                },
            ],
            'element identifier, has parent' => [
                'fixture' => '/form.html',
                'model' => new NamedDomIdentifier(
                    (new DomIdentifier('input', 1))
                        ->withParentIdentifier(new DomIdentifier('form[action="/action2"]')),
                    new VariablePlaceholder('ELEMENT')
                ),
                'resultAssertions' => function (WebDriverElementCollection $collection) {
                    $this->assertCount(1, $collection);

                    $element = $collection->current();
                    $this->assertEquals('', $element->getAttribute('test'));
                },
            ],
        ];
    }
}
