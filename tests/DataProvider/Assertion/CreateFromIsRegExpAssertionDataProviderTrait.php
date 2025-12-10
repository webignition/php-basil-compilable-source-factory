<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Metadata\Metadata as TestMetadata;
use webignition\BasilCompilableSourceFactory\Model\Block\ClassDependencyCollection;
use webignition\BasilCompilableSourceFactory\Model\ClassName;
use webignition\BasilCompilableSourceFactory\Model\ClassNameCollection;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\VariableDependencyCollection;
use webignition\BasilCompilableSourceFactory\VariableNames;
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
                'metadata' => new TestMetaData(
                    'step name',
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
                    {{ PHPUNIT }}->setExaminedValue("/^value/" ?? null);
                    {{ PHPUNIT }}->setBooleanExpectedValue(
                        @preg_match({{ PHPUNIT }}->getExaminedValue(), null) === false
                    );
                    {{ PHPUNIT }}->assertFalse(
                        {{ PHPUNIT }}->getBooleanExpectedValue(),
                        '{
                            \"step\": \"step name\",
                            \"statement\": \"$\\\".selector\\\" matches \\\"\\/^value\\/\\\"\"
                        }'
                    );
                    EOD,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
            'derived is-regexp, matches assertion with elemental value' => [
                'assertion' => new DerivedValueOperationAssertion(
                    $assertionParser->parse('$".selector" matches $".pattern-container"'),
                    '$".pattern-container"',
                    'is-regexp'
                ),
                'metadata' => new TestMetaData(
                    'step name',
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
                    {{ PHPUNIT }}->setExaminedValue((function () {
                        $element = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson('{
                            "locator": ".pattern-container"
                        }'));
                    
                        return {{ INSPECTOR }}->getValue($element);
                    })());
                    {{ PHPUNIT }}->setBooleanExpectedValue(
                        @preg_match({{ PHPUNIT }}->getExaminedValue(), null) === false
                    );
                    {{ PHPUNIT }}->assertFalse(
                        {{ PHPUNIT }}->getBooleanExpectedValue(),
                        '{
                            \"step\": \"step name\",
                            \"statement\": \"$\\\".selector\\\" matches $\\\".pattern-container\\\"\"
                        }'
                    );
                    EOD,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection(
                        new ClassNameCollection([
                            new ClassName(ElementIdentifier::class),
                        ])
                    ),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                        VariableNames::WEBDRIVER_ELEMENT_INSPECTOR,
                    ]),
                ]),
            ],
            'derived is-regexp, matches assertion with attribute value' => [
                'assertion' => new DerivedValueOperationAssertion(
                    $assertionParser->parse('$".selector" matches $".pattern-container".attribute_name'),
                    '$".pattern-container".attribute_name',
                    'is-regexp'
                ),
                'metadata' => new TestMetaData(
                    'step name',
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
                    {{ PHPUNIT }}->setExaminedValue((function () {
                        $element = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson('{
                            "locator": ".pattern-container"
                        }'));
                    
                        return $element->getAttribute('attribute_name');
                    })());
                    {{ PHPUNIT }}->setBooleanExpectedValue(
                        @preg_match({{ PHPUNIT }}->getExaminedValue(), null) === false
                    );
                    {{ PHPUNIT }}->assertFalse(
                        {{ PHPUNIT }}->getBooleanExpectedValue(),
                        '{
                            \"step\": \"step name\",
                            \"statement\": \"$\\\".selector\\\" matches $\\\".pattern-container\\\".attribute_name\"
                        }'
                    );
                    EOD,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection(
                        new ClassNameCollection([
                            new ClassName(ElementIdentifier::class),
                        ])
                    ),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
            'derived is-regexp, matches assertion with data parameter scalar value' => [
                'assertion' => new DerivedValueOperationAssertion(
                    $assertionParser->parse('$page.title matches $data.pattern'),
                    '$data.pattern',
                    'is-regexp'
                ),
                'metadata' => new TestMetaData(
                    'step name',
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
                    {{ PHPUNIT }}->setExaminedValue($pattern ?? null);
                    {{ PHPUNIT }}->setBooleanExpectedValue(
                        @preg_match({{ PHPUNIT }}->getExaminedValue(), null) === false
                    );
                    {{ PHPUNIT }}->assertFalse(
                        {{ PHPUNIT }}->getBooleanExpectedValue(),
                        '{
                            \"step\": \"step name\",
                            \"statement\": \"$page.title matches $data.pattern\"
                        }'
                    );
                    EOD,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                    ]),
                ]),
            ],
        ];
    }
}
