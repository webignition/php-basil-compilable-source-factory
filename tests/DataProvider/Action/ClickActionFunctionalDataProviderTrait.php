<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\Metadata;
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
                'additionalSetupStatements' => [
                    '$navigator = Navigator::create($crawler);',
                    '$this->assertEquals("Click", self::$client->getTitle());',
                ],
                'teardownStatements' => [
                    '$this->assertEquals("Test fixture web server default document", self::$client->getTitle());',
                ],
                'additionalVariableIdentifiers' => [
                    'ELEMENT' => self::ELEMENT_VARIABLE_NAME,
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::PHPUNIT_TEST_CASE => self::PHPUNIT_TEST_CASE_VARIABLE_NAME,
                ],
                'metadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(Navigator::class),
                    ]))
            ],
            'interaction action (click), submit button' => [
                'fixture' => '/action-click-submit.html',
                'action' => $actionFactory->createFromActionString('click "#form input[type=\'submit\']"'),
                'additionalSetupStatements' => [
                    '$navigator = Navigator::create($crawler);',
                    '$this->assertEquals("Click", self::$client->getTitle());',
                    '$submitButton = $crawler->filter(\'#form input[type="submit"]\')->getElement(0);',
                    '$this->assertEquals("false", $submitButton->getAttribute(\'data-clicked\'));',
                ],
                'teardownStatements' => [
                    '$this->assertEquals("true", $submitButton->getAttribute(\'data-clicked\'));',
                ],
                'additionalVariableIdentifiers' => [
                    'ELEMENT' => self::ELEMENT_VARIABLE_NAME,
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::PHPUNIT_TEST_CASE => self::PHPUNIT_TEST_CASE_VARIABLE_NAME,
                ],
                'metadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(Navigator::class),
                    ]))
            ],
        ];
    }
}
