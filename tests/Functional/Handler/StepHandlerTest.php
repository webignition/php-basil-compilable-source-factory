<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Handler;

use webignition\BasilCompilableSourceFactory\Handler\StepHandler;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\LineList;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilCompilationSource\Statement;
use webignition\BasilModel\Step\Step;
use webignition\BasilModel\Step\StepInterface;
use webignition\BasilModelFactory\Action\ActionFactory;
use webignition\BasilModelFactory\AssertionFactory;
use webignition\WebDriverElementInspector\Inspector;

class StepHandlerTest extends AbstractHandlerTest
{
    protected function createHandler(): HandlerInterface
    {
        return StepHandler::createHandler();
    }
    /**
     * @dataProvider createSourceDataProvider
     */
    public function testCreateSource(
        string $fixture,
        StepInterface $step,
        ?LineList $additionalSetupStatements = null,
        ?LineList $additionalTeardownStatements = null,
        array $additionalVariableIdentifiers = [],
        ?MetadataInterface $additionalMetadata = null
    ) {
        $source = $this->handler->createSource($step);

        $variableIdentifiers = array_merge(
            $additionalVariableIdentifiers,
            [
                VariableNames::PHPUNIT_TEST_CASE => self::PHPUNIT_TEST_CASE_VARIABLE_NAME,
            ]
        );

        $metadata = $additionalMetadata ?? new Metadata();
        $metadata = $this->addNavigatorToMetadata($metadata);

        $executableCall = $this->createExecutableCallForRequest(
            $fixture,
            $source,
            $additionalSetupStatements,
            $additionalTeardownStatements,
            $variableIdentifiers,
            $metadata
        );

        eval($executableCall);
    }

    public function createSourceDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();
        $assertionFactory = AssertionFactory::createFactory();

        $additionalSetupStatements = new LineList([
            new Statement('$navigator = Navigator::create($crawler)'),
            new Statement('$inspector = Inspector::create()'),
        ]);

        return [
            'single click action' => [
                'fixture' => '/action-click-submit.html',
                'model' => new Step(
                    [
                        $actionFactory->createFromActionString('click "#link-to-index"'),
                    ],
                    []
                ),
                'additionalSetupStatements' => $additionalSetupStatements,
                'additionalTeardownStatements' => new LineList([
                    new Statement(
                        '$this->assertEquals("Test fixture web server default document", self::$client->getTitle())'
                    )
                ]),
                'additionalVariableIdentifiers' => [
                    'ELEMENT' => '$element',
                    'HAS' => '$has',
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                ],
                'additionalMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(Inspector::class),
                    ]))
            ],
            'single is assertion' => [
                'fixture' => '/assertions.html',
                'model' => new Step(
                    [],
                    [
                        $assertionFactory->createFromAssertionString('".selector" is ".selector content"')
                    ]
                ),
                'additionalSetupStatements' => $additionalSetupStatements,
                'additionalTeardownStatements' => null,
                'additionalVariableIdentifiers' => [
                    'HAS' => '$has',
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                    VariableNames::WEBDRIVER_ELEMENT_INSPECTOR => self::WEBDRIVER_ELEMENT_INSPECTOR_VARIABLE_NAME,
                ],
                'additionalMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(Inspector::class),
                    ]))
            ],
            'single click action, single assertion' => [
                'fixture' => '/action-click-submit.html',
                'model' => new Step(
                    [
                        $actionFactory->createFromActionString('click "#link-to-index"'),
                    ],
                    [
                        $assertionFactory->createFromAssertionString(
                            '$page.title is "Test fixture web server default document"'
                        )
                    ]
                ),
                'additionalSetupStatements' => $additionalSetupStatements,
                'additionalTeardownStatements' => null,
                'additionalVariableIdentifiers' => [
                    'ELEMENT' => '$element',
                    'HAS' => '$has',
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                ],
                'additionalMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(Inspector::class),
                    ]))
            ],
            'multiple actions, multiple assertions' => [
                'fixture' => '/form.html',
                'model' => new Step(
                    [
                        $actionFactory->createFromActionString(
                            'click "input[name=radio-not-checked][value=not-checked-2]"'
                        ),
                        $actionFactory->createFromActionString(
                            'click "input[name=radio-checked][value=checked-3]"'
                        ),
                    ],
                    [
                        $assertionFactory->createFromAssertionString(
                            '"input[name=radio-not-checked]" is "not-checked-2"'
                        ),
                        $assertionFactory->createFromAssertionString(
                            '"input[name=radio-checked]" is "checked-3"'
                        ),
                    ]
                ),
                'additionalSetupStatements' => $additionalSetupStatements,
                'additionalTeardownStatements' => null,
                'additionalVariableIdentifiers' => [
                    'ELEMENT' => '$element',
                    'HAS' => '$has',
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::EXPECTED_VALUE => self::EXPECTED_VALUE_VARIABLE_NAME,
                    VariableNames::EXAMINED_VALUE => self::EXAMINED_VALUE_VARIABLE_NAME,
                    VariableNames::WEBDRIVER_ELEMENT_INSPECTOR => self::WEBDRIVER_ELEMENT_INSPECTOR_VARIABLE_NAME,
                ],
                'additionalMetadata' => (new Metadata())
                    ->withClassDependencies(new ClassDependencyCollection([
                        new ClassDependency(Inspector::class),
                    ]))
            ],
        ];
    }
}
