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
use webignition\BasilModels\Parser\AssertionParser;
use webignition\DomElementIdentifier\ElementIdentifier;
use webignition\SymfonyDomCrawlerNavigator\Exception\InvalidLocatorException;

trait CreateFromIdentifierNotExistsAssertionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function createFromIdentifierNotExistsAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'not-exists comparison, element identifier examined value' => [
                'assertion' => $assertionParser->parse('$".selector" not-exists'),
                'metadata' => new TestMetadata(
                    'step name',
                    (function () {
                        $assertion = \Mockery::mock(AssertionInterface::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('$".selector" not-exists')
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
                    {{ PHPUNIT }}->assertFalse(
                        {{ PHPUNIT }}->getBooleanExaminedValue(),
                        '{
                            \"step\": \"step name\",
                            \"statement\": \"$\\\".selector\\\" not-exists\"
                        }'
                    );
                    EOD,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection(
                        new ClassNameCollection([
                            new ClassName(ElementIdentifier::class),
                            new ClassName(InvalidLocatorException::class),
                        ])
                    ),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
            'not-exists comparison, attribute identifier examined value' => [
                'assertion' => $assertionParser->parse('$".selector".attribute_name not-exists'),
                'metadata' => new TestMetadata(
                    'step name',
                    (function () {
                        $assertion = \Mockery::mock(AssertionInterface::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('$".selector".attribute_name not-exists')
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
                            \"statement\": \"$\\\".selector\\\".attribute_name not-exists\"
                        }'
                    );
                    {{ PHPUNIT }}->setBooleanExaminedValue(((function () {
                        $element = {{ NAVIGATOR }}->findOne(ElementIdentifier::fromJson('{
                            "locator": ".selector"
                        }'));
                    
                        return $element->getAttribute('attribute_name');
                    })() ?? null) !== null);
                    {{ PHPUNIT }}->assertFalse(
                        {{ PHPUNIT }}->getBooleanExaminedValue(),
                        '{
                            \"step\": \"step name\",
                            \"statement\": \"$\\\".selector\\\".attribute_name not-exists\"
                        }'
                    );
                    EOD,
                'expectedMetadata' => new Metadata([
                    Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection(
                        new ClassNameCollection([
                            new ClassName(ElementIdentifier::class),
                            new ClassName(InvalidLocatorException::class),
                        ])
                    ),
                    Metadata::KEY_VARIABLE_DEPENDENCIES => new VariableDependencyCollection([
                        VariableNames::PHPUNIT_TEST_CASE,
                        VariableNames::DOM_CRAWLER_NAVIGATOR,
                    ]),
                ]),
            ],
        ];
    }
}
