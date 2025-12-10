<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Metadata\Metadata as TestMetadata;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilCompilableSourceFactory\Model\VariableDependencyCollection;
use webignition\BasilModels\Model\Assertion\AssertionInterface;
use webignition\BasilModels\Parser\AssertionParser;

trait CreateFromScalarExistsAssertionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function createFromScalarExistsAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'exists comparison, page property examined value' => [
                'assertion' => $assertionParser->parse('$page.url exists'),
                'metadata' => new TestMetadata(
                    'step name',
                    (function () {
                        $assertion = \Mockery::mock(AssertionInterface::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('$page.url exists')
                        ;

                        return $assertion;
                    })(),
                ),
                'expectedRenderedContent' => <<<'EOD'
                    {{ PHPUNIT }}->setBooleanExaminedValue(({{ CLIENT }}->getCurrentURL() ?? null) !== null);
                    {{ PHPUNIT }}->assertTrue(
                        {{ PHPUNIT }}->getBooleanExaminedValue(),
                        '{
                            \"step\": \"step name\",
                            \"statement\": \"$page.url exists\"
                        }'
                    );
                    EOD,
                'expectedMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PANTHER_CLIENT,
                        VariableName::PHPUNIT_TEST_CASE,
                    ],
                ),
            ],
            'exists comparison, data parameter value' => [
                'assertion' => $assertionParser->parse('$data.key exists'),
                'metadata' => new TestMetadata(
                    'step name',
                    (function () {
                        $assertion = \Mockery::mock(AssertionInterface::class);
                        $assertion
                            ->shouldReceive('__toString')
                            ->andReturn('$data.key exists')
                        ;

                        return $assertion;
                    })(),
                ),
                'expectedRenderedContent' => <<<'EOD'
                    {{ PHPUNIT }}->setBooleanExaminedValue(($key ?? null) !== null);
                    {{ PHPUNIT }}->assertTrue(
                        {{ PHPUNIT }}->getBooleanExaminedValue(),
                        '{
                            \"step\": \"step name\",
                            \"statement\": \"$data.key exists\"
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
