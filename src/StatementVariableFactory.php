<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory;

use webignition\BasilCompilableSourceFactory\Model\Property;

class StatementVariableFactory
{
    public static function createFactory(): StatementVariableFactory
    {
        return new StatementVariableFactory();
    }

    public function create(int $sequenceNumber): Property
    {
        return Property::asStringVariable('statement_' . $sequenceNumber);
    }
}
