<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Body;

use webignition\BasilCompilableSourceFactory\Model\HasTypeInterface;
use webignition\BasilCompilableSourceFactory\Model\MightThrowInterface;
use webignition\Stubble\Resolvable\ResolvableInterface;

interface BodyContentInterface extends ResolvableInterface, MightThrowInterface {}
