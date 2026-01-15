<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Tests\Unit\Handler\Assertion;

use PHPUnit\Framework\TestCase;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Handler\Assertion\ExistenceAssertionHandler;
use webignition\BasilModels\Model\Assertion\Assertion;

class ExistenceAssertionHandlerTest extends TestCase
{
    public function testHandleThrowsUnsupportedContentException(): void
    {
        $assertion = new Assertion(
            'invalid exists',
            0,
            'invalid',
            'exists',
        );

        $handler = ExistenceAssertionHandler::createHandler();

        $this->expectExceptionObject(new UnsupportedContentException(
            UnsupportedContentException::TYPE_IDENTIFIER,
            'invalid'
        ));

        $handler->handle($assertion);
    }
}
