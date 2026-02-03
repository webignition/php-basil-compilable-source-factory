<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

trait InclusionAssertionFunctionalDataProviderTrait
{
    /**
     * @return array<string, array<mixed>>
     */
    public static function inclusionAssertionFunctionalDataProvider(): array
    {
        return [
            'element identifier examined value, scalar expected value' => [],
            'attribute identifier examined value, scalar expected value' => [],
            'environment examined value, scalar expected value' => [],
            'browser object examined value, scalar expected value' => [],
            'page object examined value, scalar expected value' => [],
            'element identifier examined value, element identifier expected value' => [],
            'element identifier examined value, attribute identifier expected value' => [],
            'attribute identifier examined value, environment expected value' => [],
            'attribute identifier examined value, browser object expected value' => [],
            'attribute identifier examined value, page object expected value' => [],
        ];
    }
}
