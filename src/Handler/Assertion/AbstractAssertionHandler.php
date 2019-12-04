<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Handler\Assertion;

use webignition\BasilCompilableSourceFactory\CallFactory\AssertionCallFactory;
use webignition\BasilCompilableSourceFactory\Handler\NamedDomIdentifierHandler;
use webignition\BasilCompilableSourceFactory\Handler\Value\ScalarValueHandler;
use webignition\BasilCompilableSourceFactory\ModelFactory\DomIdentifier\DomIdentifierFactory;
use webignition\BasilIdentifierAnalyser\IdentifierTypeAnalyser;

abstract class AbstractAssertionHandler
{
    protected $assertionCallFactory;
    protected $scalarValueHandler;
    protected $namedDomIdentifierHandler;
    protected $domIdentifierFactory;
    protected $identifierTypeAnalyser;

    public function __construct(
        AssertionCallFactory $assertionCallFactory,
        ScalarValueHandler $scalarValueHandler,
        NamedDomIdentifierHandler $namedDomIdentifierHandler,
        DomIdentifierFactory $domIdentifierFactory,
        IdentifierTypeAnalyser $identifierTypeAnalyser
    ) {
        $this->assertionCallFactory = $assertionCallFactory;
        $this->scalarValueHandler = $scalarValueHandler;
        $this->namedDomIdentifierHandler = $namedDomIdentifierHandler;
        $this->domIdentifierFactory = $domIdentifierFactory;
        $this->identifierTypeAnalyser = $identifierTypeAnalyser;
    }
}
