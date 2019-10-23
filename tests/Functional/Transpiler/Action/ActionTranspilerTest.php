<?php
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Functional\Transpiler\Action;

use PHPUnit\Framework\ExpectationFailedException;
use webignition\BasilCompilableSourceFactory\HandlerInterface;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\BackActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\ClickActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\ForwardActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\ReloadActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\SetActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\SubmitActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\WaitActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\DataProvider\Action\WaitForActionFunctionalDataProviderTrait;
use webignition\BasilCompilableSourceFactory\Tests\Functional\Transpiler\AbstractTranspilerTest;
use webignition\BasilCompilableSourceFactory\Transpiler\Action\ActionTranspiler;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\BasilCompilationSource\ClassDependency;
use webignition\BasilCompilationSource\ClassDependencyCollection;
use webignition\BasilCompilationSource\Metadata;
use webignition\BasilCompilationSource\MetadataInterface;
use webignition\BasilModel\Action\ActionInterface;
use webignition\BasilModel\Action\WaitAction;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Value\DomIdentifierValue;
use webignition\SymfonyDomCrawlerNavigator\Navigator;

class ActionTranspilerTest extends AbstractTranspilerTest
{
    use WaitActionFunctionalDataProviderTrait;
    use WaitForActionFunctionalDataProviderTrait;
    use BackActionFunctionalDataProviderTrait;
    use ForwardActionFunctionalDataProviderTrait;
    use ReloadActionFunctionalDataProviderTrait;
    use ClickActionFunctionalDataProviderTrait;
    use SubmitActionFunctionalDataProviderTrait;
    use SetActionFunctionalDataProviderTrait;

    protected function createTranspiler(): HandlerInterface
    {
        return ActionTranspiler::createHandler();
    }

    /**
     * @dataProvider transpileForExecutableActionsDataProvider
     */
    public function testTranspileForExecutableActions(
        string $fixture,
        ActionInterface $action,
        array $additionalSetupStatements,
        array $teardownStatements,
        array $additionalVariableIdentifiers,
        ?MetadataInterface $metadata = null
    ) {
        $source = $this->transpiler->createSource($action);

        $variableIdentifiers = array_merge(
            [
                VariableNames::PANTHER_CRAWLER => self::PANTHER_CRAWLER_VARIABLE_NAME,
            ],
            $additionalVariableIdentifiers
        );

        $executableCall = $this->createExecutableCallForRequest(
            $fixture,
            $source,
            $additionalSetupStatements,
            $teardownStatements,
            $variableIdentifiers,
            $metadata
        );

        eval($executableCall);
    }

    public function transpileForExecutableActionsDataProvider()
    {
        return [
            'wait action' => current($this->waitActionFunctionalDataProvider()),
            'wait-for action' => current($this->waitForActionFunctionalDataProvider()),
            'back action' => current($this->backActionFunctionalDataProvider()),
            'forward action' => current($this->forwardActionFunctionalDataProvider()),
            'reload action' => current($this->reloadActionFunctionalDataProvider()),
            'click action' => current($this->clickActionFunctionalDataProvider()),
            'submit action' => current($this->submitActionFunctionalDataProvider()),
            'set action' => current($this->setActionFunctionalDataProvider()),
        ];
    }

    /**
     * @dataProvider transpileForFailingActionsDataProvider
     */
    public function testTranspileForFailingActions(
        ActionInterface $action,
        array $variableIdentifiers,
        string $expectedExpectationFailedExceptionMessage
    ) {
        $source = $this->transpiler->createSource($action);

        $setupStatements = [
            '$navigator = Navigator::create($crawler);',
        ];

        $metadata = (new Metadata())
            ->withClassDependencies(new ClassDependencyCollection([
                new ClassDependency(Navigator::class),
            ]));

        $executableCall = $this->createExecutableCallForRequest(
            '/action-wait.html',
            $source,
            $setupStatements,
            [],
            $variableIdentifiers,
            $metadata
        );

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($expectedExpectationFailedExceptionMessage);

        eval($executableCall);
    }

    public function transpileForFailingActionsDataProvider(): array
    {
        return [
            'wait action, element identifier examined value, element does not exist' => [
                'action' => new WaitAction(
                    'wait $elements.element_name',
                    new DomIdentifierValue(new DomIdentifier('.non-existent'))
                ),
                'variableIdentifiers' => [
                    'DURATION' => '$duration',
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::PHPUNIT_TEST_CASE => self::PHPUNIT_TEST_CASE_VARIABLE_NAME,
                    VariableNames::WEBDRIVER_ELEMENT_INSPECTOR => self::WEBDRIVER_ELEMENT_INSPECTOR_VARIABLE_NAME,
                ],
                'expectedExpectationFailedExceptionMessage' => 'Failed asserting that false is true.',
            ],
            'wait, attribute identifier examined value, element does not exist' => [
                'action' => new WaitAction(
                    'wait $elements.element_name.attribute_name',
                    new DomIdentifierValue(
                        (new DomIdentifier('.non-existent'))->withAttributeName('attribute_name')
                    )
                ),
                'variableIdentifiers' => [
                    'DURATION' => '$duration',
                    'HAS' => self::HAS_VARIABLE_NAME,
                    VariableNames::DOM_CRAWLER_NAVIGATOR => self::DOM_CRAWLER_NAVIGATOR_VARIABLE_NAME,
                    VariableNames::PHPUNIT_TEST_CASE => self::PHPUNIT_TEST_CASE_VARIABLE_NAME,
                    VariableNames::WEBDRIVER_ELEMENT_INSPECTOR => self::WEBDRIVER_ELEMENT_INSPECTOR_VARIABLE_NAME,
                ],
                'expectedExpectationFailedExceptionMessage' => 'Failed asserting that false is true.',
            ],
        ];
    }
}
