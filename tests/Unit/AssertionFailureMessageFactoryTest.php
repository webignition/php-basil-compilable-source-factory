<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit;

use webignition\BasilCompilableSourceFactory\AssertionFailureMessageFactory;
use webignition\BasilModels\Assertion\AssertionInterface;
use webignition\BasilModels\Assertion\DerivedElementExistsAssertion;
use webignition\BasilParser\ActionParser;
use webignition\BasilParser\AssertionParser;

class AssertionFailureMessageFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AssertionFailureMessageFactory
     */
    private $assertionFailureMessageFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertionFailureMessageFactory = AssertionFailureMessageFactory::createFactory();
    }

    /**
     * @dataProvider createForAssertionDataProvider
     */
    public function testCreateForAssertion(AssertionInterface $assertion, string $expectedFailureMessage)
    {
        $this->assertSame(
            $expectedFailureMessage,
            $this->assertionFailureMessageFactory->createForAssertion($assertion)
        );
    }

    public function createForAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();
        $actionParser = ActionParser::create();

        return [
            'direct is assertion' => [
                'assertion' => $assertionParser->parse('$".selector" is "value"'),
                'expectedFailureMessage' =>
                    '{' . "\n " .
                    '   "assertion": {' . "\n" .
                    '        "source": "$\".selector\" is \"value\"",' . "\n" .
                    '        "identifier": "$\".selector\"",' . "\n" .
                    '        "comparison": "is",' . "\n" .
                    '        "value": "\"value\""' . "\n" .
                    '    }' . "\n" .
                    '}',
            ],
            'derived exists assertion from is assertion' => [
                'assertion' => new DerivedElementExistsAssertion(
                    $assertionParser->parse('$".selector" is "value"'),
                    '$".selector"'
                ),
                'expectedFailureMessage' =>
                    '{' . "\n " .
                    '   "assertion": {' . "\n" .
                    '        "source": "$\".selector\" exists",' . "\n" .
                    '        "identifier": "$\".selector\"",' . "\n" .
                    '        "comparison": "exists"' . "\n" .
                    '    },' . "\n" .
                    '    "derived_from": {' . "\n" .
                    '        "statement_type": "assertion",' . "\n" .
                    '        "statement": {' . "\n" .
                    '            "source": "$\".selector\" is \"value\"",' . "\n" .
                    '            "identifier": "$\".selector\"",' . "\n" .
                    '            "comparison": "is",' . "\n" .
                    '            "value": "\"value\""' . "\n" .
                    '        }' . "\n" .
                    '    }' . "\n" .
                    '}',
            ],
            'derived exists assertion from action' => [
                'assertion' => new DerivedElementExistsAssertion(
                    $actionParser->parse('click $".selector"'),
                    '$".selector"'
                ),
                'expectedFailureMessage' =>
                    '{' . "\n " .
                    '   "assertion": {' . "\n" .
                    '        "source": "$\".selector\" exists",' . "\n" .
                    '        "identifier": "$\".selector\"",' . "\n" .
                    '        "comparison": "exists"' . "\n" .
                    '    },' . "\n" .
                    '    "derived_from": {' . "\n" .
                    '        "statement_type": "action",' . "\n" .
                    '        "statement": {' . "\n" .
                    '            "source": "click $\".selector\"",' . "\n" .
                    '            "type": "click",' . "\n" .
                    '            "arguments": "$\".selector\"",' . "\n" .
                    '            "identifier": "$\".selector\""' . "\n" .
                    '        }' . "\n" .
                    '    }' . "\n" .
                    '}',
            ],
        ];
    }
}
