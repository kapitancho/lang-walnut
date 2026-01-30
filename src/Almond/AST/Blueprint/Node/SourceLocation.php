<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node;

use JsonSerializable;
use Stringable;
use Walnut\Lib\Walex\SourcePosition;

interface SourceLocation extends JsonSerializable, Stringable {
	public string $moduleName { get; }
	public SourcePosition $startPosition { get; }
	public SourcePosition $endPosition { get; }
}