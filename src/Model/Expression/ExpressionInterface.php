<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Expression;

use webignition\BasilCompilableSourceFactory\Model\HasMetadataInterface;
use webignition\Stubble\Resolvable\ResolvableInterface;

interface ExpressionInterface extends HasMetadataInterface, ResolvableInterface {}
