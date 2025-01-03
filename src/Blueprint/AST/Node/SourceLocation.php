<?php

namespace Walnut\Lang\Blueprint\AST\Node;

use JsonSerializable;
use Walnut\Lib\Walex\SourcePosition;

interface SourceLocation extends JsonSerializable {
	public SourcePosition $startPosition { get; }
	public SourcePosition $endPosition { get; }
}