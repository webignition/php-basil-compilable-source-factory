<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Step;

use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\Step\DerivedAssertionFactory;
use webignition\BasilModels\Model\Action\Action;
use webignition\BasilModels\Model\Action\ActionInterface;
use webignition\BasilModels\Model\Assertion\AssertionInterface;
use webignition\BasilModels\Model\Assertion\DerivedValueOperationAssertion;
use webignition\BasilModels\Model\Assertion\UniqueAssertionCollection;
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

    /**
     * @dataProvider createForActionDataProvider
     */
    public function testCreateForAction(ActionInterface $action, UniqueAssertionCollection $expectedAssertions): void
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
                'action' => $actionParser->parse('click $".selector"'),
                'expectedAssertions' => new UniqueAssertionCollection([
                    new DerivedValueOperationAssertion(
                        $actionParser->parse('click $".selector"'),
                        '$".selector"',
                        'exists'
                    ),
                ]),
            ],
            'click action, descendant identifier' => [
                'action' => $actionParser->parse('click $".parent" >> $".child"'),
                'expectedAssertions' => new UniqueAssertionCollection([
                    new DerivedValueOperationAssertion(
                        $actionParser->parse('click $".parent" >> $".child"'),
                        '$".parent"',
                        'exists'
                    ),
                    new DerivedValueOperationAssertion(
                        $actionParser->parse('click $".parent" >> $".child"'),
                        '$".parent" >> $".child"',
                        'exists'
                    ),
                ]),
            ],
            'set action' => [
                'action' => $actionParser->parse('set $".selector" to "value"'),
                'expectedAssertions' => new UniqueAssertionCollection([
                    new DerivedValueOperationAssertion(
                        $actionParser->parse('set $".selector" to "value"'),
                        '$".selector"',
                        'exists'
                    ),
                ]),
            ],
            'set action, descendant identifier' => [
                'action' => $actionParser->parse('set $".parent" >> $".child" to "value"'),
                'expectedAssertions' => new UniqueAssertionCollection([
                    new DerivedValueOperationAssertion(
                        $actionParser->parse('set $".parent" >> $".child" to "value"'),
                        '$".parent"',
                        'exists'
                    ),
                    new DerivedValueOperationAssertion(
                        $actionParser->parse('set $".parent" >> $".child" to "value"'),
                        '$".parent" >> $".child"',
                        'exists'
                    ),
                ]),
            ],
            'set action, elemental value' => [
                'action' => $actionParser->parse('set $".selector" to $".value"'),
                'expectedAssertions' => new UniqueAssertionCollection([
                    new DerivedValueOperationAssertion(
                        $actionParser->parse('set $".selector" to $".value"'),
                        '$".selector"',
                        'exists'
                    ),
                    new DerivedValueOperationAssertion(
                        $actionParser->parse('set $".selector" to $".value"'),
                        '$".value"',
                        'exists'
                    ),
                ]),
            ],
            'set action, elemental value matches identifier' => [
                'action' => $actionParser->parse('set $".selector" to $".selector"'),
                'expectedAssertions' => new UniqueAssertionCollection([
                    new DerivedValueOperationAssertion(
                        $actionParser->parse('set $".selector" to $".selector"'),
                        '$".selector"',
                        'exists'
                    ),
                ]),
            ],
            'wait action, elemental duration' => [
                'action' => $actionParser->parse('wait $".duration"'),
                'expectedAssertions' => new UniqueAssertionCollection([
                    new DerivedValueOperationAssertion(
                        $actionParser->parse('wait $".duration"'),
                        '$".duration"',
                        'exists'
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
            'exists assertion' => [
                'assertion' => $assertionParser->parse('$".selector" exists'),
                'expectedAssertions' => new UniqueAssertionCollection([]),
            ],
            'not-exists assertion' => [
                'assertion' => $assertionParser->parse('$".selector" not-exists'),
                'expectedAssertions' => new UniqueAssertionCollection([]),
            ],
            'exists assertion, descendant identifier' => [
                'assertion' => $assertionParser->parse('$".parent" >> $".child" exists'),
                'expectedAssertions' => new UniqueAssertionCollection([
                    new DerivedValueOperationAssertion(
                        $assertionParser->parse('$".parent" >> $".child" exists'),
                        '$".parent"',
                        'exists'
                    ),
                ]),
            ],
            'is assertion' => [
                'assertion' => $assertionParser->parse('$".selector" is "value"'),
                'expectedAssertions' => new UniqueAssertionCollection([
                    new DerivedValueOperationAssertion(
                        $assertionParser->parse('$".selector" is "value"'),
                        '$".selector"',
                        'exists'
                    ),
                ]),
            ],
            'is assertion, elemental value' => [
                'assertion' => $assertionParser->parse('$".selector" is $".value"'),
                'expectedAssertions' => new UniqueAssertionCollection([
                    new DerivedValueOperationAssertion(
                        $assertionParser->parse('$".selector" is $".value"'),
                        '$".selector"',
                        'exists'
                    ),
                    new DerivedValueOperationAssertion(
                        $assertionParser->parse('$".selector" is $".value"'),
                        '$".value"',
                        'exists'
                    ),
                ]),
            ],
            'matches, scalar to scalar' => [
                'assertion' => $assertionParser->parse('$page.title matches "pattern"'),
                'expectedAssertions' => new UniqueAssertionCollection([
                    new DerivedValueOperationAssertion(
                        $assertionParser->parse('$page.title matches "pattern"'),
                        '"pattern"',
                        'is-regexp'
                    )
                ]),
            ],
            'matches, scalar to elemental' => [
                'assertion' => $assertionParser->parse('$page.title matches $".pattern-container"'),
                'expectedAssertions' => new UniqueAssertionCollection([
                    new DerivedValueOperationAssertion(
                        $assertionParser->parse('$page.title matches $".pattern-container"'),
                        '$".pattern-container"',
                        'exists'
                    ),
                    new DerivedValueOperationAssertion(
                        $assertionParser->parse('$page.title matches $".pattern-container"'),
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
