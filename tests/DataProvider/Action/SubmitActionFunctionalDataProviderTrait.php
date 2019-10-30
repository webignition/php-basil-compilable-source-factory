<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action;

use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilModelFactory\Action\ActionFactory;
use webignition\SymfonyDomCrawlerNavigator\Navigator;

trait SubmitActionFunctionalDataProviderTrait
{
    public function submitActionFunctionalDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();

        $fixture = '/action-click-submit.html';

        $setupStatements = new LineList([
            new Statement('$navigator = Navigator::create($crawler)'),
            new Statement('$this->assertEquals("Click", self::$client->getTitle())'),
            new Statement('$submitButton = $crawler->filter(\'#form input[type="submit"]\')->getElement(0)'),
            new Statement('$form = $crawler->filter(\'#form\')->getElement(0)'),
            new Statement('$this->assertEquals("false", $submitButton->getAttribute(\'data-submitted\'))'),
            new Statement('$this->assertEquals("false", $form->getAttribute(\'data-submitted\'))'),
        ]);

        $teardownStatements = new LineList([
            new Statement('$this->assertEquals("false", $submitButton->getAttribute(\'data-submitted\'))'),
            new Statement('$this->assertEquals("true", $form->getAttribute(\'data-submitted\'))'),
        ]);

        $variableIdentifiers = [
            'HAS' => self::HAS_VARIABLE_NAME,
            'ELEMENT' => self::ELEMENT_VARIABLE_NAME,
            VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
            VariableNames::PHPUNIT_TEST_CASE => self::PHPUNIT_TEST_CASE_VARIABLE_NAME,
        ];

        $metadata = new Metadata();
        $metadata->addClassDependencies(new ClassDependencyCollection([
            new ClassDependency(Navigator::class),
        ]));

        return [
            'interaction action (submit), form submit button' => [
                'fixture' => $fixture,
                'action' => $actionFactory->createFromActionString('submit "#form input[type=\'submit\']"'),
                'additionalSetupStatements' => $setupStatements,
                'teardownStatements' => $teardownStatements,
                'additionalVariableIdentifiers' => $variableIdentifiers,
                'metadata' => $metadata,
            ],
            'interaction action (submit), form' => [
                'fixture' => $fixture,
                'action' => $actionFactory->createFromActionString('submit "#form"'),
                'additionalSetupStatements' => $setupStatements,
                'teardownStatements' => $teardownStatements,
                'additionalVariableIdentifiers' => $variableIdentifiers,
                'metadata' => $metadata,
            ],
        ];
    }
}
