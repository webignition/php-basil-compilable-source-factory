<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Step;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\Step\DerivedAssertionFactory;
use webignition\BasilModels\Model\Statement\Action\Action;
use webignition\BasilModels\Model\Statement\Action\ActionInterface;
use webignition\BasilModels\Model\Statement\Assertion\AssertionCollection;
use webignition\BasilModels\Model\Statement\Assertion\AssertionCollectionInterface;
use webignition\BasilModels\Model\Statement\Assertion\AssertionInterface;
use webignition\BasilModels\Model\Statement\Assertion\DerivedValueOperationAssertion;
use webignition\BasilModels\Parser\ActionParser;
use webignition\BasilModels\Parser\AssertionParser;

class DerivedAssertionFactoryTest extends TestCase
{
    private DerivedAssertionFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = DerivedAssertionFactory::createFactory();
    }

    #[DataProvider('createForActionDataProvider')]
    public function testCreateForAction(ActionInterface $action, AssertionCollectionInterface $expectedAssertions): void
    {
        $this->assertEquals(
            $expectedAssertions,
            $this->factory->createForAction($action)
        );
    }

    /**
     * @return array<mixed>
     */
    public static function createForActionDataProvider(): array
    {
        $actionParser = ActionParser::create();

        return [
            'click action' => [
                'action' => $actionParser->parse('click $".selector"', 0),
                'expectedAssertions' => new AssertionCollection([
                    new DerivedValueOperationAssertion(
                        $actionParser->parse('click $".selector"', 0),
                        '$".selector"',
                        'exists'
                    ),
                ]),
            ],
            'click action, descendant identifier' => [
                'action' => $actionParser->parse('click $".parent" >> $".child"', 0),
                'expectedAssertions' => new AssertionCollection([
                    new DerivedValueOperationAssertion(
                        $actionParser->parse('click $".parent" >> $".child"', 0),
                        '$".parent"',
                        'exists'
                    ),
                    new DerivedValueOperationAssertion(
                        $actionParser->parse('click $".parent" >> $".child"', 0),
                        '$".parent" >> $".child"',
                        'exists'
                    ),
                ]),
            ],
            'set action' => [
                'action' => $actionParser->parse('set $".selector" to "value"', 0),
                'expectedAssertions' => new AssertionCollection([
                    new DerivedValueOperationAssertion(
                        $actionParser->parse('set $".selector" to "value"', 0),
                        '$".selector"',
                        'exists'
                    ),
                ]),
            ],
            'set action, descendant identifier' => [
                'action' => $actionParser->parse('set $".parent" >> $".child" to "value"', 0),
                'expectedAssertions' => new AssertionCollection([
                    new DerivedValueOperationAssertion(
                        $actionParser->parse('set $".parent" >> $".child" to "value"', 0),
                        '$".parent"',
                        'exists'
                    ),
                    new DerivedValueOperationAssertion(
                        $actionParser->parse('set $".parent" >> $".child" to "value"', 0),
                        '$".parent" >> $".child"',
                        'exists'
                    ),
                ]),
            ],
            'set action, elemental value' => [
                'action' => $actionParser->parse('set $".selector" to $".value"', 0),
                'expectedAssertions' => new AssertionCollection([
                    new DerivedValueOperationAssertion(
                        $actionParser->parse('set $".selector" to $".value"', 0),
                        '$".selector"',
                        'exists'
                    ),
                    new DerivedValueOperationAssertion(
                        $actionParser->parse('set $".selector" to $".value"', 0),
                        '$".value"',
                        'exists'
                    ),
                ]),
            ],
            'set action, elemental value matches identifier' => [
                'action' => $actionParser->parse('set $".selector" to $".selector"', 0),
                'expectedAssertions' => new AssertionCollection([
                    new DerivedValueOperationAssertion(
                        $actionParser->parse('set $".selector" to $".selector"', 0),
                        '$".selector"',
                        'exists'
                    ),
                    new DerivedValueOperationAssertion(
                        $actionParser->parse('set $".selector" to $".selector"', 0),
                        '$".selector"',
                        'exists'
                    ),
                ]),
            ],
            'wait action, elemental duration' => [
                'action' => $actionParser->parse('wait $".duration"', 0),
                'expectedAssertions' => new AssertionCollection([
                    new DerivedValueOperationAssertion(
                        $actionParser->parse('wait $".duration"', 0),
                        '$".duration"',
                        'exists'
                    ),
                ]),
            ],
        ];
    }

    #[DataProvider('createForAssertionDataProvider')]
    public function testCreateForAssertion(
        AssertionInterface $assertion,
        AssertionCollectionInterface $expectedAssertions
    ): void {
        $this->assertEquals(
            $expectedAssertions,
            $this->factory->createForAssertion($assertion)
        );
    }

    /**
     * @return array<mixed>
     */
    public static function createForAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'element exists assertion' => [
                'assertion' => $assertionParser->parse('$".selector" exists', 0),
                'expectedAssertions' => new AssertionCollection([]),
            ],
            'attribute exists assertion' => [
                'assertion' => $assertionParser->parse('$".selector".attribute_name exists', 0),
                'expectedAssertions' => new AssertionCollection([
                    new DerivedValueOperationAssertion(
                        $assertionParser->parse('$".selector".attribute_name exists', 0),
                        '$".selector"',
                        'exists'
                    ),
                ]),
            ],
            'element not-exists assertion' => [
                'assertion' => $assertionParser->parse('$".selector" not-exists', 0),
                'expectedAssertions' => new AssertionCollection([]),
            ],
            'element exists assertion, descendant identifier' => [
                'assertion' => $assertionParser->parse('$".parent" >> $".child" exists', 0),
                'expectedAssertions' => new AssertionCollection([
                    new DerivedValueOperationAssertion(
                        $assertionParser->parse('$".parent" >> $".child" exists', 0),
                        '$".parent"',
                        'exists'
                    ),
                ]),
            ],
            'is assertion' => [
                'assertion' => $assertionParser->parse('$".selector" is "value"', 0),
                'expectedAssertions' => new AssertionCollection([
                    new DerivedValueOperationAssertion(
                        $assertionParser->parse('$".selector" is "value"', 0),
                        '$".selector"',
                        'exists'
                    ),
                ]),
            ],
            'is assertion, elemental value' => [
                'assertion' => $assertionParser->parse('$".selector" is $".value"', 0),
                'expectedAssertions' => new AssertionCollection([
                    new DerivedValueOperationAssertion(
                        $assertionParser->parse('$".selector" is $".value"', 0),
                        '$".selector"',
                        'exists'
                    ),
                    new DerivedValueOperationAssertion(
                        $assertionParser->parse('$".selector" is $".value"', 0),
                        '$".value"',
                        'exists'
                    ),
                ]),
            ],
            'matches, scalar to scalar' => [
                'assertion' => $assertionParser->parse('$page.title matches "pattern"', 0),
                'expectedAssertions' => new AssertionCollection([
                    new DerivedValueOperationAssertion(
                        $assertionParser->parse('$page.title matches "pattern"', 0),
                        '"pattern"',
                        'is-regexp'
                    )
                ]),
            ],
            'matches, scalar to elemental' => [
                'assertion' => $assertionParser->parse('$page.title matches $".pattern-container"', 0),
                'expectedAssertions' => new AssertionCollection([
                    new DerivedValueOperationAssertion(
                        $assertionParser->parse('$page.title matches $".pattern-container"', 0),
                        '$".pattern-container"',
                        'exists'
                    ),
                    new DerivedValueOperationAssertion(
                        $assertionParser->parse('$page.title matches $".pattern-container"', 0),
                        '$".pattern-container"',
                        'is-regexp'
                    )
                ]),
            ],
        ];
    }

    public function testCreateForClickActionThrowsException(): void
    {
        $action = new Action(
            'click "foo"',
            0,
            'click',
            '"foo"',
            '"foo"'
        );

        $this->expectExceptionObject(
            new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, '"foo"')
        );

        $this->factory->createForAction($action);
    }

    public function testCreateForSetActionThrowsException(): void
    {
        $action = new Action(
            'set "foo" to "value"',
            0,
            'set',
            '"foo" to "value"',
            '"foo"',
            '"value"'
        );

        $this->expectExceptionObject(
            new UnsupportedContentException(UnsupportedContentException::TYPE_IDENTIFIER, '"foo"')
        );

        $this->factory->createForAction($action);
    }
}
