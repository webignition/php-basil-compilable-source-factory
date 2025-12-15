<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Metadata\Metadata as TestMetadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilModels\Model\Action\ActionInterface;
use webignition\BasilModels\Model\Assertion\AssertionInterface;
use webignition\BasilModels\Model\Assertion\DerivedValueOperationAssertion;
use webignition\BasilModels\Parser\ActionParser;
use webignition\BasilModels\Parser\AssertionParser;
use webignition\DomElementIdentifier\ElementIdentifier;
use webignition\SymfonyDomCrawlerNavigator\Exception\InvalidLocatorException;

trait CreateFromIdentifierExistsAssertionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function createFromIdentifierExistsAssertionDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $assertionParser = AssertionParser::create();

        $expectedMetadata = new Metadata(
            classNames: [
                ElementIdentifier::class,
                InvalidLocatorException::class,
            ],
            variableNames: [
                VariableName::DOM_CRAWLER_NAVIGATOR,
                VariableName::PHPUNIT_TEST_CASE,
            ],
        );

        return [
            'exists comparison, element identifier examined value' => [
                'assertion' => $assertionParser->parse('$".selector" exists'),
                'metadata' => new TestMetadata(
                    (function () {
                        $assertion = \Mockery::mock(AssertionInterface::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('$".selector" exists')
                        ;

                        return $assertion;
                    })(),
                ),
                'expectedRenderedContent' => <<<'EOD'
                    {{ PHPUNIT }}->examinedElementIdentifier = ElementIdentifier::fromJson('{
                        "locator": ".selector"
                    }');
                    try {
                        {{ PHPUNIT }}->setBooleanExaminedValue(
                            {{ NAVIGATOR }}->has({{ PHPUNIT }}->examinedElementIdentifier)
                        );
                    } catch (InvalidLocatorException $exception) {
                        {{ PHPUNIT }}->fail('Invalid locator');
                    }
                    {{ PHPUNIT }}->assertTrue(
                        {{ PHPUNIT }}->getBooleanExaminedValue(),
                        '{
                            \"assertion\": \"$\\\".selector\\\" exists\"
                        }'
                    );
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
            'exists comparison, attribute identifier examined value' => [
                'assertion' => $assertionParser->parse('$".selector".attribute_name exists'),
                'metadata' => new TestMetadata(
                    (function () {
                        $assertion = \Mockery::mock(AssertionInterface::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('$".selector".attribute_name exists')
                        ;

                        return $assertion;
                    })(),
                ),
                'expectedRenderedContent' => <<<'EOD'
                    {{ PHPUNIT }}->examinedElementIdentifier = ElementIdentifier::fromJson('{
                        "locator": ".selector"
                    }');
                    try {
                        {{ PHPUNIT }}->setBooleanExaminedValue(
                            {{ NAVIGATOR }}->hasOne({{ PHPUNIT }}->examinedElementIdentifier)
                        );
                    } catch (InvalidLocatorException $exception) {
                        {{ PHPUNIT }}->fail('Invalid locator');
                    }
                    {{ PHPUNIT }}->assertTrue(
                        {{ PHPUNIT }}->getBooleanExaminedValue(),
                        '{
                            \"assertion\": \"$\\\".selector\\\".attribute_name exists\"
                        }'
                    );
                    {{ PHPUNIT }}->setBooleanExaminedValue(((function () {
                        $element = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson('{
                            "locator": ".selector"
                        }'));

                        return $element->getAttribute('attribute_name');
                    })() ?? null) !== null);
                    {{ PHPUNIT }}->assertTrue(
                        {{ PHPUNIT }}->getBooleanExaminedValue(),
                        '{
                            \"assertion\": \"$\\\".selector\\\".attribute_name exists\"
                        }'
                    );
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
            'exists comparison, css attribute selector containing dot' => [
                'assertion' => $assertionParser->parse('$"a[href=foo.html]" exists'),
                'metadata' => new TestMetadata(
                    (function () {
                        $assertion = \Mockery::mock(AssertionInterface::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('$"a[href=foo.html]" exists')
                        ;

                        return $assertion;
                    })(),
                ),
                'expectedRenderedContent' => <<<'EOD'
                    {{ PHPUNIT }}->examinedElementIdentifier = ElementIdentifier::fromJson('{
                        "locator": "a[href=foo.html]"
                    }');
                    try {
                        {{ PHPUNIT }}->setBooleanExaminedValue(
                            {{ NAVIGATOR }}->has({{ PHPUNIT }}->examinedElementIdentifier)
                        );
                    } catch (InvalidLocatorException $exception) {
                        {{ PHPUNIT }}->fail('Invalid locator');
                    }
                    {{ PHPUNIT }}->assertTrue(
                        {{ PHPUNIT }}->getBooleanExaminedValue(),
                        '{
                            \"assertion\": \"$\\\"a[href=foo.html]\\\" exists\"
                        }'
                    );
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
            'exists comparison, css attribute selector containing dot with attribute name' => [
                'assertion' => $assertionParser->parse('$"a[href=foo.html]".attribute_name exists'),
                'metadata' => new TestMetadata(
                    (function () {
                        $assertion = \Mockery::mock(AssertionInterface::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('$"a[href=foo.html]".attribute_name exists')
                        ;

                        return $assertion;
                    })(),
                ),
                'expectedRenderedContent' => <<<'EOD'
                    {{ PHPUNIT }}->examinedElementIdentifier = ElementIdentifier::fromJson('{
                        "locator": "a[href=foo.html]"
                    }');
                    try {
                        {{ PHPUNIT }}->setBooleanExaminedValue(
                            {{ NAVIGATOR }}->hasOne({{ PHPUNIT }}->examinedElementIdentifier)
                        );
                    } catch (InvalidLocatorException $exception) {
                        {{ PHPUNIT }}->fail('Invalid locator');
                    }
                    {{ PHPUNIT }}->assertTrue(
                        {{ PHPUNIT }}->getBooleanExaminedValue(),
                        '{
                            \"assertion\": \"$\\\"a[href=foo.html]\\\".attribute_name exists\"
                        }'
                    );
                    {{ PHPUNIT }}->setBooleanExaminedValue(((function () {
                        $element = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson('{
                            "locator": "a[href=foo.html]"
                        }'));

                        return $element->getAttribute('attribute_name');
                    })() ?? null) !== null);
                    {{ PHPUNIT }}->assertTrue(
                        {{ PHPUNIT }}->getBooleanExaminedValue(),
                        '{
                            \"assertion\": \"$\\\"a[href=foo.html]\\\".attribute_name exists\"
                        }'
                    );
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
            'derived exists comparison, click action source' => [
                'assertion' => new DerivedValueOperationAssertion(
                    $actionParser->parse('click $".selector"'),
                    '$".selector"',
                    'exists'
                ),
                'metadata' => new TestMetadata(
                    (function () {
                        $action = \Mockery::mock(ActionInterface::class);
                        $action
                            ->shouldReceive('__toString')
                            ->andReturn('click $".selector"')
                        ;

                        $assertion = \Mockery::mock(DerivedValueOperationAssertion::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('$".selector" exists')
                        ;

                        $assertion
                            ->shouldReceive('getSourceStatement')
                            ->andReturn($action)
                        ;

                        return $assertion;
                    })(),
                ),
                'expectedRenderedContent' => <<<'EOD'
                    {{ PHPUNIT }}->examinedElementIdentifier = ElementIdentifier::fromJson('{
                        "locator": ".selector"
                    }');
                    try {
                        {{ PHPUNIT }}->setBooleanExaminedValue(
                            {{ NAVIGATOR }}->hasOne({{ PHPUNIT }}->examinedElementIdentifier)
                        );
                    } catch (InvalidLocatorException $exception) {
                        {{ PHPUNIT }}->fail('Invalid locator');
                    }
                    {{ PHPUNIT }}->assertTrue(
                        {{ PHPUNIT }}->getBooleanExaminedValue(),
                        '{
                            \"assertion\": \"$\\\".selector\\\" exists\",
                            \"source\": \"click $\\\".selector\\\"\"
                        }'
                    );
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
            'derived exists comparison, submit action source' => [
                'assertion' => new DerivedValueOperationAssertion(
                    $actionParser->parse('submit $".selector"'),
                    '$".selector"',
                    'exists'
                ),
                'metadata' => new TestMetadata(
                    (function () {
                        $action = \Mockery::mock(ActionInterface::class);
                        $action
                            ->shouldReceive('__toString')
                            ->andReturn('submit $".selector"')
                        ;

                        $assertion = \Mockery::mock(DerivedValueOperationAssertion::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('$".selector" exists')
                        ;

                        $assertion
                            ->shouldReceive('getSourceStatement')
                            ->andReturn($action)
                        ;

                        return $assertion;
                    })(),
                ),
                'expectedRenderedContent' => <<<'EOD'
                    {{ PHPUNIT }}->examinedElementIdentifier = ElementIdentifier::fromJson('{
                        "locator": ".selector"
                    }');
                    try {
                        {{ PHPUNIT }}->setBooleanExaminedValue(
                            {{ NAVIGATOR }}->hasOne({{ PHPUNIT }}->examinedElementIdentifier)
                        );
                    } catch (InvalidLocatorException $exception) {
                        {{ PHPUNIT }}->fail('Invalid locator');
                    }
                    {{ PHPUNIT }}->assertTrue(
                        {{ PHPUNIT }}->getBooleanExaminedValue(),
                        '{
                            \"assertion\": \"$\\\".selector\\\" exists\",
                            \"source\": \"submit $\\\".selector\\\"\"
                        }'
                    );
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
            'derived exists comparison, set action source' => [
                'assertion' => new DerivedValueOperationAssertion(
                    $actionParser->parse('set $".selector" to "value"'),
                    '$".selector"',
                    'exists'
                ),
                'metadata' => new TestMetadata(
                    (function () {
                        $action = \Mockery::mock(ActionInterface::class);
                        $action
                            ->shouldReceive('__toString')
                            ->andReturn('set $".selector" to "value"')
                        ;

                        $assertion = \Mockery::mock(DerivedValueOperationAssertion::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('$".selector" exists')
                        ;

                        $assertion
                            ->shouldReceive('getSourceStatement')
                            ->andReturn($action)
                        ;

                        return $assertion;
                    })(),
                ),
                'expectedRenderedContent' => <<<'EOD'
                    {{ PHPUNIT }}->examinedElementIdentifier = ElementIdentifier::fromJson('{
                        "locator": ".selector"
                    }');
                    try {
                        {{ PHPUNIT }}->setBooleanExaminedValue(
                            {{ NAVIGATOR }}->has({{ PHPUNIT }}->examinedElementIdentifier)
                        );
                    } catch (InvalidLocatorException $exception) {
                        {{ PHPUNIT }}->fail('Invalid locator');
                    }
                    {{ PHPUNIT }}->assertTrue(
                        {{ PHPUNIT }}->getBooleanExaminedValue(),
                        '{
                            \"assertion\": \"$\\\".selector\\\" exists\",
                            \"source\": \"set $\\\".selector\\\" to \\\"value\\\"\"
                        }'
                    );
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
            'derived exists comparison, wait action source' => [
                'assertion' => new DerivedValueOperationAssertion(
                    $actionParser->parse('wait $".duration"'),
                    '$".duration"',
                    'exists'
                ),
                'metadata' => new TestMetadata(
                    (function () {
                        $action = \Mockery::mock(ActionInterface::class);
                        $action
                            ->shouldReceive('__toString')
                            ->andReturn('wait $".duration"')
                        ;

                        $assertion = \Mockery::mock(DerivedValueOperationAssertion::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('$".duration" exists')
                        ;

                        $assertion
                            ->shouldReceive('getSourceStatement')
                            ->andReturn($action)
                        ;

                        return $assertion;
                    })(),
                ),
                'expectedRenderedContent' => <<<'EOD'
                    {{ PHPUNIT }}->examinedElementIdentifier = ElementIdentifier::fromJson('{
                        "locator": ".duration"
                    }');
                    try {
                        {{ PHPUNIT }}->setBooleanExaminedValue(
                            {{ NAVIGATOR }}->has({{ PHPUNIT }}->examinedElementIdentifier)
                        );
                    } catch (InvalidLocatorException $exception) {
                        {{ PHPUNIT }}->fail('Invalid locator');
                    }
                    {{ PHPUNIT }}->assertTrue(
                        {{ PHPUNIT }}->getBooleanExaminedValue(),
                        '{
                            \"assertion\": \"$\\\".duration\\\" exists\",
                            \"source\": \"wait $\\\".duration\\\"\"
                        }'
                    );
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
        ];
    }
}
