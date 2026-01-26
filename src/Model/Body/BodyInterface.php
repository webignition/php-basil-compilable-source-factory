<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Body;

use webignition\BasilCompilableSourceFactory\Model\HasMetadataInterface;
use webignition\BasilCompilableSourceFactory\Model\MightThrowInterface;

interface BodyInterface extends BodyContentInterface, HasMetadataInterface {}
