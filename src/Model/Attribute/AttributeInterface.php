<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Attribute;

use webignition\BasilCompilableSourceFactory\Model\HasMetadataInterface;
use webignition\Stubble\Resolvable\ResolvableInterface;

interface AttributeInterface extends HasMetadataInterface, ResolvableInterface {}
