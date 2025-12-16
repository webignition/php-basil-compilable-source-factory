<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Metadata\Metadata as TestMetadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilModels\Model\Assertion\AssertionInterface;
use webignition\BasilModels\Model\Assertion\DerivedValueOperationAssertion;
use webignition\BasilModels\Parser\AssertionParser;
use webignition\DomElementIdentifier\ElementIdentifier;

trait CreateFromIsRegExpAssertionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function createFromIsRegExpAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'derived is-regexp, matches assertion with literal scalar value' => [
                'assertion' => new DerivedValueOperationAssertion(
                    $assertionParser->parse('$".selector" matches "/^value/"'),
                    '"/^value/"',
                    'is-regexp'
                ),
                'metadata' => new TestMetadata(
                    (function () {
                        $assertion = \Mockery::mock(AssertionInterface::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('$".selector" matches "/^value/"')
                        ;

                        return $assertion;
                    })(),
                ),
                'expectedRenderedContent' => <<<'EOD'
                    $examinedValue = "/^value/" ?? null;
                    $expectedValue = @preg_match($examinedValue, null) === false;
                    {{ PHPUNIT }}->assertFalse(
                        {{ PHPUNIT }}->getBooleanExpectedValue(),
                        '{
                            \"assertion\": \"$\\\".selector\\\" matches \\\"\\/^value\\/\\\"\"
                        }'
                    );
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'derived is-regexp, matches assertion with elemental value' => [
                'assertion' => new DerivedValueOperationAssertion(
                    $assertionParser->parse('$".selector" matches $".pattern-container"'),
                    '$".pattern-container"',
                    'is-regexp'
                ),
                'metadata' => new TestMetadata(
                    (function () {
                        $assertion = \Mockery::mock(AssertionInterface::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('$".selector" matches $".pattern-container"')
                        ;

                        return $assertion;
                    })(),
                ),
                'expectedRenderedContent' => <<<'EOD'
                    $examinedValue = (function () {
                        $element = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson('{
                            "locator": ".pattern-container"
                        }'));
                    
                        return {{ INSPECTOR }}->getValue($element);
                    })();
                    $expectedValue = @preg_match($examinedValue, null) === false;
                    {{ PHPUNIT }}->assertFalse(
                        {{ PHPUNIT }}->getBooleanExpectedValue(),
                        '{
                            \"assertion\": \"$\\\".selector\\\" matches $\\\".pattern-container\\\"\"
                        }'
                    );
                    EOD,
                'expectedMetadata' => new Metadata(
                    classNames: [
                        ElementIdentifier::class,
                    ],
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::WEBDRIVER_ELEMENT_INSPECTOR,
                    ],
                ),
            ],
            'derived is-regexp, matches assertion with attribute value' => [
                'assertion' => new DerivedValueOperationAssertion(
                    $assertionParser->parse('$".selector" matches $".pattern-container".attribute_name'),
                    '$".pattern-container".attribute_name',
                    'is-regexp'
                ),
                'metadata' => new TestMetadata(
                    (function () {
                        $assertion = \Mockery::mock(AssertionInterface::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('$".selector" matches $".pattern-container".attribute_name')
                        ;

                        return $assertion;
                    })(),
                ),
                'expectedRenderedContent' => <<<'EOD'
                    $examinedValue = (function () {
                        $element = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson('{
                            "locator": ".pattern-container"
                        }'));
                    
                        return $element->getAttribute('attribute_name');
                    })();
                    $expectedValue = @preg_match($examinedValue, null) === false;
                    {{ PHPUNIT }}->assertFalse(
                        {{ PHPUNIT }}->getBooleanExpectedValue(),
                        '{
                            \"assertion\": \"$\\\".selector\\\" matches $\\\".pattern-container\\\".attribute_name\"
                        }'
                    );
                    EOD,
                'expectedMetadata' => new Metadata(
                    classNames: [
                        ElementIdentifier::class,
                    ],
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                    ],
                ),
            ],
            'derived is-regexp, matches assertion with data parameter scalar value' => [
                'assertion' => new DerivedValueOperationAssertion(
                    $assertionParser->parse('$page.title matches $data.pattern'),
                    '$data.pattern',
                    'is-regexp'
                ),
                'metadata' => new TestMetadata(
                    (function () {
                        $assertion = \Mockery::mock(AssertionInterface::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('$page.title matches $data.pattern')
                        ;

                        return $assertion;
                    })(),
                ),
                'expectedRenderedContent' => <<<'EOD'
                    $examinedValue = $pattern ?? null;
                    $expectedValue = @preg_match($examinedValue, null) === false;
                    {{ PHPUNIT }}->assertFalse(
                        {{ PHPUNIT }}->getBooleanExpectedValue(),
                        '{
                            \"assertion\": \"$page.title matches $data.pattern\"
                        }'
                    );
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
        ];
    }
}
