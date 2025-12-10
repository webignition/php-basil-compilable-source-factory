<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Metadata\Metadata as TestMetadata;
use webignition\BasilCompilableSourceFactory\Model\Block\ClassDependencyCollection;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\ClassNameCollection;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\VariableDependencyCollection;
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
                    'step name',
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
                        self::staticSetLastException($exception);
                        {{ PHPUNIT }}->fail('Invalid locator');
                    }
                    {{ PHPUNIT }}->assertTrue(
                        {{ PHPUNIT }}->getBooleanExaminedValue(),
                        '{
                            \"step\": \"step name\",
                            \"statement\": \"$\\\".selector\\\" exists\"
                        }'
                    );
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
            'exists comparison, attribute identifier examined value' => [
                'assertion' => $assertionParser->parse('$".selector".attribute_name exists'),
                'metadata' => new TestMetadata(
                    'step name',
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
                        self::staticSetLastException($exception);
                        {{ PHPUNIT }}->fail('Invalid locator');
                    }
                    {{ PHPUNIT }}->assertTrue(
                        {{ PHPUNIT }}->getBooleanExaminedValue(),
                        '{
                            \"step\": \"step name\",
                            \"statement\": \"$\\\".selector\\\".attribute_name exists\"
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
                            \"step\": \"step name\",
                            \"statement\": \"$\\\".selector\\\".attribute_name exists\"
                        }'
                    );
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
            'exists comparison, css attribute selector containing dot' => [
                'assertion' => $assertionParser->parse('$"a[href=foo.html]" exists'),
                'metadata' => new TestMetadata(
                    'step name',
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
                        self::staticSetLastException($exception);
                        {{ PHPUNIT }}->fail('Invalid locator');
                    }
                    {{ PHPUNIT }}->assertTrue(
                        {{ PHPUNIT }}->getBooleanExaminedValue(),
                        '{
                            \"step\": \"step name\",
                            \"statement\": \"$\\\"a[href=foo.html]\\\" exists\"
                        }'
                    );
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
            'exists comparison, css attribute selector containing dot with attribute name' => [
                'assertion' => $assertionParser->parse('$"a[href=foo.html]".attribute_name exists'),
                'metadata' => new TestMetadata(
                    'step name',
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
                        self::staticSetLastException($exception);
                        {{ PHPUNIT }}->fail('Invalid locator');
                    }
                    {{ PHPUNIT }}->assertTrue(
                        {{ PHPUNIT }}->getBooleanExaminedValue(),
                        '{
                            \"step\": \"step name\",
                            \"statement\": \"$\\\"a[href=foo.html]\\\".attribute_name exists\"
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
                            \"step\": \"step name\",
                            \"statement\": \"$\\\"a[href=foo.html]\\\".attribute_name exists\"
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
                    'step name',
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
                            {{ NAVIGATOR }}->hasOne({{ PHPUNIT }}->examinedElementIdentifier)
                        );
                    } catch (InvalidLocatorException $exception) {
                        self::staticSetLastException($exception);
                        {{ PHPUNIT }}->fail('Invalid locator');
                    }
                    {{ PHPUNIT }}->assertTrue(
                        {{ PHPUNIT }}->getBooleanExaminedValue(),
                        '{
                            \"step\": \"step name\",
                            \"statement\": \"$\\\".selector\\\" exists\"
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
                    'step name',
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
                            {{ NAVIGATOR }}->hasOne({{ PHPUNIT }}->examinedElementIdentifier)
                        );
                    } catch (InvalidLocatorException $exception) {
                        self::staticSetLastException($exception);
                        {{ PHPUNIT }}->fail('Invalid locator');
                    }
                    {{ PHPUNIT }}->assertTrue(
                        {{ PHPUNIT }}->getBooleanExaminedValue(),
                        '{
                            \"step\": \"step name\",
                            \"statement\": \"$\\\".selector\\\" exists\"
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
                    'step name',
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
                        self::staticSetLastException($exception);
                        {{ PHPUNIT }}->fail('Invalid locator');
                    }
                    {{ PHPUNIT }}->assertTrue(
                        {{ PHPUNIT }}->getBooleanExaminedValue(),
                        '{
                            \"step\": \"step name\",
                            \"statement\": \"$\\\".selector\\\" exists\"
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
                    'step name',
                    (function () {
                        $assertion = \Mockery::mock(AssertionInterface::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('$".duration" exists')
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
                        self::staticSetLastException($exception);
                        {{ PHPUNIT }}->fail('Invalid locator');
                    }
                    {{ PHPUNIT }}->assertTrue(
                        {{ PHPUNIT }}->getBooleanExaminedValue(),
                        '{
                            \"step\": \"step name\",
                            \"statement\": \"$\\\".duration\\\" exists\"
                        }'
                    );
                    EOD,
                'expectedMetadata' => $expectedMetadata,
            ],
        ];
    }
}
