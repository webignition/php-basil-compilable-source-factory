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
                'statement' => $assertionParser->parse('$page.url exists', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $examinedValue = (bool) (({{ CLIENT }}->getCurrentURL() ?? null) !== null);
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertTrue(
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage(
                            '{
                                "statement-type": "assertion",
                                "source": "$page.url exists",
                                "index": 0,
                                "identifier": "$page.url",
                                "operator": "exists"
                            }',
                            true,
                            $examinedValue,
                        ),
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PANTHER_CLIENT,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::MESSAGE_FACTORY,
                    ],
                ),
            ],
            'exists comparison, data parameter value' => [
                'statement' => $assertionParser->parse('$data.key exists', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $examinedValue = (bool) (($key ?? null) !== null);
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertTrue(
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage(
                            '{
                                "statement-type": "assertion",
                                "source": "$data.key exists",
                                "index": 0,
                                "identifier": "$data.key",
                                "operator": "exists"
                            }',
                            true,
                            $examinedValue,
                        ),
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(),
                'expectedBodyMetadata' => new Metadata(
                    variableNames: [
                        VariableName::PHPUNIT_TEST_CASE,
                        VariableName::MESSAGE_FACTORY,
                    ],
                ),
            ],
        ];
    }
}
