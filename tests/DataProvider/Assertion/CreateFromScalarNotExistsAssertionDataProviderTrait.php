<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Model\Metadata\Metadata;
use webignition\BasilModels\Parser\AssertionParser;

trait CreateFromScalarNotExistsAssertionDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function createFromScalarNotExistsAssertionDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

        return [
            'not-exists comparison, page property examined value' => [
                'statement' => $assertionParser->parse('$page.url not-exists', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $examinedValue = (bool) (({{ CLIENT }}->getCurrentURL() ?? null) !== null);
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertFalse(
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage(
                            '{
                                "statement-type": "assertion",
                                "source": "$page.url not-exists",
                                "index": 0,
                                "identifier": "$page.url",
                                "operator": "not-exists"
                            }',
                            false,
                            $examinedValue,
                        ),
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PANTHER_CLIENT,
                    ],
                ),
                'expectedBodyMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PHPUNIT_TEST_CASE,
                        DependencyName::MESSAGE_FACTORY,
                    ],
                ),
            ],
            'not-exists comparison, data parameter value' => [
                'statement' => $assertionParser->parse('$data.key not-exists', 0),
                'expectedRenderedSetup' => <<< 'EOD'
                    $examinedValue = (bool) (($key ?? null) !== null);
                    EOD,
                'expectedRenderedBody' => <<< 'EOD'
                    {{ PHPUNIT }}->assertFalse(
                        $examinedValue,
                        {{ MESSAGE_FACTORY }}->createAssertionMessage(
                            '{
                                "statement-type": "assertion",
                                "source": "$data.key not-exists",
                                "index": 0,
                                "identifier": "$data.key",
                                "operator": "not-exists"
                            }',
                            false,
                            $examinedValue,
                        ),
                    );
                    EOD,
                'expectedSetupMetadata' => new Metadata(),
                'expectedBodyMetadata' => new Metadata(
                    dependencyNames: [
                        DependencyName::PHPUNIT_TEST_CASE,
                        DependencyName::MESSAGE_FACTORY,
                    ],
                ),
            ],
        ];
    }
}
