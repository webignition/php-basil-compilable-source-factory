<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Step;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\Step\FooDerivedAssertionFactory;
use webignition\BasilModels\Action\ActionInterface;
use webignition\BasilModels\Action\InputAction;
use webignition\BasilModels\Action\InteractionAction;
use webignition\BasilModels\Assertion\AssertionInterface;
use webignition\BasilModels\Assertion\DerivedElementExistsAssertion;
use webignition\BasilModels\Assertion\UniqueAssertionCollection;
use webignition\BasilParser\ActionParser;
use webignition\BasilParser\AssertionParser;

class FooDerivedAssertionFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var FooDerivedAssertionFactory
     */
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = FooDerivedAssertionFactory::createFactory();
    }

    public function testCreateFactory()
    {
        $this->assertInstanceOf(FooDerivedAssertionFactory::class, FooDerivedAssertionFactory::createFactory());
    }

    /**
     * @dataProvider createForActionDataProvider
     */
    public function testCreateForAction(ActionInterface $action, UniqueAssertionCollection $expectedAssertions)
    {
        $this->assertEquals(
            $expectedAssertions,
            $this->factory->createForAction($action)
        );
    }

    public function createForActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'click action' => [
                'action' => $actionParser->parse('click $".selector"'),
                'expectedAssertions' => new UniqueAssertionCollection([
                    new DerivedElementExistsAssertion(
                        $actionParser->parse('click $".selector"'),
                        '$".selector"'
                    ),
                ]),
            ],
            'click action, descendant identifier' => [
                'action' => $actionParser->parse('click $"{{ $".parent" }} .child"'),
                'expectedAssertions' => new UniqueAssertionCollection([
                    new DerivedElementExistsAssertion(
                        $actionParser->parse('click $"{{ $".parent" }} .child"'),
                        '$".parent"'
                    ),
                    new DerivedElementExistsAssertion(
                        $actionParser->parse('click $"{{ $".parent" }} .child"'),
                        '$"{{ $".parent" }} .child"'
                    ),
                ]),
            ],
            'set action' => [
                'action' => $actionParser->parse('set $".selector" to "value"'),
                'expectedAssertions' => new UniqueAssertionCollection([
                    new DerivedElementExistsAssertion(
                        $actionParser->parse('set $".selector" to "value"'),
                        '$".selector"'
                    ),
                ]),
            ],
            'set action, descendant identifier' => [
                'action' => $actionParser->parse('set $"{{ $".parent" }} .child" to "value"'),
                'expectedAssertions' => new UniqueAssertionCollection([
                    new DerivedElementExistsAssertion(
                        $actionParser->parse('set $"{{ $".parent" }} .child" to "value"'),
                        '$".parent"'
                    ),
                    new DerivedElementExistsAssertion(
                        $actionParser->parse('set $"{{ $".parent" }} .child" to "value"'),
                        '$"{{ $".parent" }} .child"'
                    ),
                ]),
            ],
            'set action, elemental value' => [
                'action' => $actionParser->parse('set $".selector" to $".value"'),
                'expectedAssertions' => new UniqueAssertionCollection([
                    new DerivedElementExistsAssertion(
                        $actionParser->parse('set $".selector" to $".value"'),
                        '$".selector"'
                    ),
                    new DerivedElementExistsAssertion(
                        $actionParser->parse('set $".selector" to $".value"'),
                        '$".value"'
                    ),
                ]),
            ],
            'set action, elemental value matches identifier' => [
                'action' => $actionParser->parse('set $".selector" to $".selector"'),
                'expectedAssertions' => new UniqueAssertionCollection([
                    new DerivedElementExistsAssertion(
                        $actionParser->parse('set $".selector" to $".selector"'),
                        '$".selector"'
                    ),
                ]),
            ],
            'wait action, elemental duration' => [
                'action' => $actionParser->parse('wait $".duration"'),
                'expectedAssertions' => new UniqueAssertionCollection([
                    new DerivedElementExistsAssertion(
                        $actionParser->parse('wait $".duration"'),
                        '$".duration"'
                    ),
                ]),
            ],
        ];
    }

    /**
     * @dataProvider createForAssertionDataProvider
     */
    public function testCreateForAssertion(
        AssertionInterface $assertion,
        UniqueAssertionCollection $expectedAssertions
    ) {
        $this->assertEquals(
            $expectedAssertions,
            $this->factory->createForAssertion($assertion)
        );
    }

    public function createForAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'exists assertion' => [
                'assertion' => $assertionParser->parse('$".selector" exists'),
                'expectedAssertions' => new UniqueAssertionCollection([]),
            ],
            'not-exists assertion' => [
                'assertion' => $assertionParser->parse('$".selector" not-exists'),
                'expectedAssertions' => new UniqueAssertionCollection([]),
            ],
            'exists assertion, descendant identifier' => [
                'assertion' => $assertionParser->parse('$"{{ $".parent" }} .child" exists'),
                'expectedAssertions' => new UniqueAssertionCollection([
                    new DerivedElementExistsAssertion(
                        $assertionParser->parse('$"{{ $".parent" }} .child" exists'),
                        '$".parent"'
                    ),
                ]),
            ],
            'is assertion' => [
                'assertion' => $assertionParser->parse('$".selector" is "value"'),
                'expectedAssertions' => new UniqueAssertionCollection([
                    new DerivedElementExistsAssertion(
                        $assertionParser->parse('$".selector" is "value"'),
                        '$".selector"'
                    ),
                ]),
            ],
            'is assertion, elemental value' => [
                'assertion' => $assertionParser->parse('$".selector" is $".value"'),
                'expectedAssertions' => new UniqueAssertionCollection([
                    new DerivedElementExistsAssertion(
                        $assertionParser->parse('$".selector" is $".value"'),
                        '$".selector"'
                    ),
                    new DerivedElementExistsAssertion(
                        $assertionParser->parse('$".selector" is $".value"'),
                        '$".value"'
                    ),
                ]),
            ],
        ];
    }

    public function testCreateForClickActionThrowsException()
    {
        $action = new InteractionAction(
            'click "foo"',
            'click',
            '"foo"',
            '"foo"'
        );

        $this->expectExceptionObject(
            new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, '"foo"')
        );

        $this->factory->createForAction($action);
    }

    public function testCreateForSetActionThrowsException()
    {
        $action = new InputAction(
            'set "foo" to "value"',
            'set',
            '"foo"',
            '"value"'
        );

        $this->expectExceptionObject(
            new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, '"foo"')
        );

        $this->factory->createForAction($action);
    }
}
