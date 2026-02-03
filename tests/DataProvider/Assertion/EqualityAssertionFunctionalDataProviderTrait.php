<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\DataProvider\Assertion;

trait EqualityAssertionFunctionalDataProviderTrait
{
    /**
     * @return array<string, array{"fixture": string}>
     */
    public static function equalityAssertionFunctionalDataProvider(): array
    {
        return [
            'element identifier examined value, scalar expected value' => [
                'fixture' => '/assertions.html',
            ],
            'attribute identifier examined value, scalar expected value' => [
                'fixture' => '/assertions.html',
            ],
            'environment examined value, scalar expected value' => [
                'fixture' => '/empty.html',
            ],
            'browser object examined value, scalar expected value' => [
                'fixture' => '/empty.html',
            ],
            'page object examined value, scalar expected value' => [
                'fixture' => '/index.html',
            ],
            'element identifier examined value, element identifier expected value' => [
                'fixture' => '/assertions.html',
            ],
            'element identifier examined value, attribute identifier expected value' => [
                'fixture' => '/assertions.html',
            ],
            'attribute identifier examined value, environment expected value' => [
                'fixture' => '/assertions.html',
            ],
            'attribute identifier examined value, browser object expected value' => [
                'fixture' => '/assertions.html',
            ],
            'attribute identifier examined value, page object expected value' => [
                'fixture' => '/assertions.html',
            ],
            'select element identifier examined value, scalar expected value (1)' => [
                'fixture' => '/form.html',
            ],
            'select element identifier examined value, scalar expected value (2)' => [
                'fixture' => '/form.html',
            ],
            'option collection element identifier examined value, scalar expected value (1)' => [
                'fixture' => '/form.html',
            ],
            'option collection element identifier examined value, scalar expected value (2)' => [
                'fixture' => '/form.html',
            ],
            'radio group element identifier examined value, scalar expected value (1)' => [
                'fixture' => '/form.html',
            ],
            'radio group element identifier examined value, scalar expected value (2)' => [
                'fixture' => '/form.html',
            ],
        ];
    }
}
