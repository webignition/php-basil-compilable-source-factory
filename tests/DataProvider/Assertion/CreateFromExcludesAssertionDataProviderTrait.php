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
use webignition\BasilModels\Parser\AssertionParser;
use webignition\DomElementIdentifier\ElementIdentifier;

trait CreateFromExcludesAssertionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function createFromExcludesAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'excludes comparison, element identifier examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$".selector" excludes "value"'),
                'metadata' => new TestMetadata(
                    'step name',
                    (function () {
                        $assertion = \Mockery::mock(AssertionInterface::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('$".selector" excludes "value"')
                        ;

                        return $assertion;
                    })(),
                ),
                'expectedRenderedContent' => <<<'EOD'
                    {{ PHPUNIT }}->setExpectedValue("value" ?? null);
                    {{ PHPUNIT }}->setExaminedValue((function () {
                        $element = {{ NAVIGATOR }}->find(ElementIdentifier::fromJson('{
                            "locator": ".selector"
                        }'));
                    
                        return {{ INSPECTOR }}->getValue($element);
                    })());
                    {{ PHPUNIT }}->assertStringNotContainsString(
                        (string) ({{ PHPUNIT }}->getExpectedValue()),
                        (string) ({{ PHPUNIT }}->getExaminedValue()),
                        '{
                            \"step\": \"step name\",
                            \"statement\": \"$\\\".selector\\\" excludes \\\"value\\\"\"
                        }'
                    );
                    EOD,
                'expectedMetadata' => new Metadata(
                    classNames: [
                        ElementIdentifier::class,
                    ],
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::WEBDRIVER_ELEMENT_INSPECTOR,
                    ],
                ),
            ],
            'excludes comparison, attribute identifier examined value, literal string expected value' => [
                'assertion' => $assertionParser->parse('$".selector".attribute_name excludes "value"'),
                'metadata' => new TestMetadata(
                    'step name',
                    (function () {
                        $assertion = \Mockery::mock(AssertionInterface::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('$".selector".attribute_name excludes "value"')
                        ;

                        return $assertion;
                    })(),
                ),
                'expectedRenderedContent' => <<<'EOD'
                    {{ PHPUNIT }}->setExpectedValue("value" ?? null);
                    {{ PHPUNIT }}->setExaminedValue((function () {
                        $element = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson('{
                            "locator": ".selector"
                        }'));
                    
                        return $element->getAttribute('attribute_name');
                    })());
                    {{ PHPUNIT }}->assertStringNotContainsString(
                        (string) ({{ PHPUNIT }}->getExpectedValue()),
                        (string) ({{ PHPUNIT }}->getExaminedValue()),
                        '{
                            \"step\": \"step name\",
                            \"statement\": \"$\\\".selector\\\".attribute_name excludes \\\"value\\\"\"
                        }'
                    );
                    EOD,
                'expectedMetadata' => new Metadata(
                    classNames: [
                        ElementIdentifier::class,
                    ],
                    variableNames: [
                        VariableName::DOM_CRAWLER_NAVIGATOR,
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
        ];
    }
}
