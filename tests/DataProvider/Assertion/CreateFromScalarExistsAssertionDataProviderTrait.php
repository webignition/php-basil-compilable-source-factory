<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Enum\VariableName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
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
                'expectedRenderedContent' => <<<'EOD'
                    {{ PHPUNIT }}->assertTrue(
                        ({{ CLIENT }}->getCurrentURL() ?? null) !== null,
                        '{
                            "statement": "$page.url exists"
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
                'expectedRenderedContent' => <<<'EOD'
                    {{ PHPUNIT }}->assertTrue(
                        ($key ?? null) !== null,
                        '{
                            "statement": "$data.key exists"
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
