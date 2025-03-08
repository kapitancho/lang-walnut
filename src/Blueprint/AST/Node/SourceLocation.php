<?php

namespace Walnut\Lang\Blueprint\AST\Node;

use JsonSerializable;
use Stringable;
use Walnut\Lib\Walex\SourcePosition;

interface SourceLocation extends JsonSerializable, Stringable {
	public string $moduleName { get; }
	public SourcePosition $startPosition { get; }
	public SourcePosition $endPosition { get; }
}