<?php

namespace Walnut\Lang\Implementation\AST\Node;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation as SourceLocationInterface;
use Walnut\Lib\Walex\SourcePosition;

final readonly class SourceLocation implements SourceLocationInterface {
	public function __construct(
		public SourcePosition $startPosition,
		public SourcePosition $endPosition,
	) {}

	public function jsonSerialize(): string {
		return sprintf("%d:%d:%d-%d:%d:%d",
			$this->startPosition->line, $this->startPosition->column, $this->startPosition->offset,
			$this->endPosition->line, $this->endPosition->column, $this->endPosition->offset
		);
	}
}