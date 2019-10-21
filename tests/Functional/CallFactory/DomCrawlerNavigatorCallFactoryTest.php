<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\CallFactory;

use Facebook\WebDriver\WebDriverElement;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractBrowserTestCase;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Identifier\DomIdentifierInterface;
use webignition\SymfonyDomCrawlerNavigator\Navigator;
use webignition\WebDriverElementCollection\WebDriverElementCollectionInterface;

class DomCrawlerNavigatorCallFactoryTest extends AbstractBrowserTestCase
{
    /**
     * @var DomCrawlerNavigatorCallFactory
     */
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = DomCrawlerNavigatorCallFactory::createFactory();
    }

    /**
     * @dataProvider createFindCallDataProvider
     */
    public function testCreateFindCall(
        string $fixture,
        DomIdentifierInterface $identifier,
        callable $assertions
    ) {
        $source = $this->factory->createFindCall($identifier);

        $variableIdentifiers = [
            VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
        ];

        $metadata = $this->addNavigatorToMetadata(new Metadata());

        $executableCall = $this->createExecutableCallForRequestWithReturn(
            $fixture,
            $source,
            [
                '$navigator = Navigator::create($crawler);',
            ],
            $variableIdentifiers,
            $metadata
        );

        $returnValue = eval($executableCall);

        $assertions($returnValue);
    }

    public function createFindCallDataProvider(): array
    {
        return [
            'no parent, no ordinal position' => [
                'fixture' => '/form.html',
                'identifier' => new DomIdentifier('input'),
                'assertions' => function (WebDriverElementCollectionInterface $collection) {
                    $this->assertCount(9, $collection);
                },
            ],
            'no parent, has ordinal position' => [
                'fixture' => '/form.html',
                'identifier' => new DomIdentifier('input', 1),
                'assertions' => function (WebDriverElementCollectionInterface $collection) {
                    $this->assertCount(1, $collection);

                    $element = $collection->get(0);
                    $this->assertInstanceOf(WebDriverElement::class, $element);

                    if ($element instanceof WebDriverElement) {
                        $this->assertSame('input-without-value', $element->getAttribute('name'));
                    }
                },
            ],
            'has parent' => [
                'fixture' => '/form.html',
                'identifier' => (new DomIdentifier('input'))
                    ->withParentIdentifier(new DomIdentifier('form[action="/action2"]')),
                'assertions' => function (WebDriverElementCollectionInterface $collection) {
                    $this->assertCount(1, $collection);

                    $element = $collection->get(0);
                    $this->assertInstanceOf(WebDriverElement::class, $element);

                    if ($element instanceof WebDriverElement) {
                        $this->assertSame('input-2', $element->getAttribute('name'));
                    }
                },
            ],
        ];
    }
}
