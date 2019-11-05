<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\Tests\Services\PlaceholderFactory;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilModelFactory\Action\ActionFactory;
use webignition\SymfonyDomCrawlerNavigator\Navigator;

trait ClickActionFunctionalDataProviderTrait
{
    public function clickActionFunctionalDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        return [
            'interaction action (click), link' => [
                'fixture' => '/action-click-submit.html',
                'action' => $actionFactory->createFromActionString('click "#link-to-index"'),
                'additionalSetupStatements' => new LineList([
                    new Statement(sprintf(
                        '%s->assertEquals("Click", %s->getTitle())',
                        PlaceholderFactory::phpUnitTestCase(),
                        PlaceholderFactory::pantherClient()
                    )),
                ]),
                'teardownStatements' => new LineList([
                    new Statement(sprintf(
                        '%s->assertEquals("Test fixture web server default document", %s->getTitle())',
                        PlaceholderFactory::phpUnitTestCase(),
                        PlaceholderFactory::pantherClient()
                    )),
                ]),
                'additionalVariableIdentifiers' => [
                    'ELEMENT' => self::ELEMENT_VARIABLE_NAME,
                    'HAS' => self::HAS_VARIABLE_NAME,
                ]
            ],
            'interaction action (click), submit button' => [
                'fixture' => '/action-click-submit.html',
                'action' => $actionFactory->createFromActionString('click "#form input[type=\'submit\']"'),
                'additionalSetupStatements' => new LineList([
                    new Statement(sprintf(
                        '%s->assertEquals("Click", %s->getTitle())',
                        PlaceholderFactory::phpUnitTestCase(),
                        PlaceholderFactory::pantherClient()
                    )),
                    new Statement(sprintf(
                        '$submitButton = %s->filter(\'#form input[type="submit"]\')->getElement(0)',
                        PlaceholderFactory::pantherCrawler()
                    )),
                    new Statement(sprintf(
                        '%s->assertEquals("false", $submitButton->getAttribute(\'data-clicked\'))',
                        PlaceholderFactory::phpUnitTestCase()
                    )),
                ]),
                'teardownStatements' => new LineList([
                    new Statement(sprintf(
                        '$submitButton = %s->filter(\'#form input[type="submit"]\')->getElement(0)',
                        PlaceholderFactory::pantherCrawler()
                    )),
                    new Statement(sprintf(
                        '%s->assertEquals("true", $submitButton->getAttribute(\'data-clicked\'))',
                        PlaceholderFactory::phpUnitTestCase()
                    )),
                ]),
                'additionalVariableIdentifiers' => [
                    'ELEMENT' => self::ELEMENT_VARIABLE_NAME,
                    'HAS' => self::HAS_VARIABLE_NAME,
                ],
                'metadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(Navigator::class),
                    ]))
            ],
        ];
    }
}
