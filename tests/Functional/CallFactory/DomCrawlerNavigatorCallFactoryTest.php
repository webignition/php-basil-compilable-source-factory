<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\CallFactory;

use Facebook\WebDriver\WebDriverElement;
use webignition\BasilCompilableSourceFactory\CallFactory\DomCrawlerNavigatorCallFactory;
use webignition\BasilCompilableSourceFactory\Tests\Functional\AbstractBrowserTestCase;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Identifier\DomIdentifierInterface;
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
        $statement = $this->factory->createFindCall($identifier);

        $variableIdentifiers = [
            VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
        ];

        $metadata = $this->addNavigatorToMetadata(new Metadata());

        $code = $this->createExecutableCallForRequestWithReturn(
            $fixture,
            new LineList([$statement]),
            new LineList([
                new Statement('$navigator = Navigator::create($crawler)'),
            ]),
            null,
            $variableIdentifiers,
            $metadata
        );

        $returnValue = eval($code);

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
