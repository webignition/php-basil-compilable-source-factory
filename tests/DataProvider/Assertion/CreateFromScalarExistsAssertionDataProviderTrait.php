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
                'assertion' => $assertionParser->parse('$page.url exists', 0),
                'expectedRenderedContent' => <<<'EOD'
                    $examinedValue = ({{ CLIENT }}->getCurrentURL() ?? null) !== null;

                    {{ PHPUNIT }}->assertTrue(
                        $examinedValue,
                        '{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$page.url exists",
                                "index": 0,
                                "identifier": "$page.url",
                                "operator": "exists"
                            },
                            "expected": ' . (true ? 'true' : 'false') . ',
                            "examined": ' . ($examinedValue ? 'true' : 'false') . '
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
                'assertion' => $assertionParser->parse('$data.key exists', 0),
                'expectedRenderedContent' => <<<'EOD'
                    $examinedValue = ($key ?? null) !== null;

                    {{ PHPUNIT }}->assertTrue(
                        $examinedValue,
                        '{
                            "statement": {
                                "statement-type": "assertion",
                                "source": "$data.key exists",
                                "index": 0,
                                "identifier": "$data.key",
                                "operator": "exists"
                            },
                            "expected": ' . (true ? 'true' : 'false') . ',
                            "examined": ' . ($examinedValue ? 'true' : 'false') . '
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
