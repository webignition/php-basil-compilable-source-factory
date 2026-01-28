<?php

declare(strict_types=1);

namespace webignition\BasilCompilableSourceFactory\Model\Expression;

use webignition\BasilCompilableSourceFactory\Model\HasMetadataInterface as HasMetadata;
use webignition\BasilCompilableSourceFactory\Model\HasTypeInterface as HasType;
use webignition\BasilCompilableSourceFactory\Model\IsStaticInterface as IsStatic;
use webignition\BasilCompilableSourceFactory\Model\MightThrowInterface as MightThrow;
use webignition\Stubble\Resolvable\ResolvableInterface;

interface ExpressionInterface extends HasMetadata, ResolvableInterface, MightThrow, IsStatic, HasType {}
