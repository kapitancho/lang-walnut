<?php

namespace Walnut\Lang\Implementation\AST\Node;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation as SourceLocationInterface;
use Walnut\Lib\Walex\SourcePosition;

final readonly class SourceLocation implements SourceLocationInterface {
	public function __construct(
		public string $moduleName,
		public SourcePosition $startPosition,
		public SourcePosition $endPosition,
	) {}

	public function __toString(): string {
		return sprintf("function defined in module %s, starting on line %d, column %d",
			$this->moduleName,
			$this->startPosition->line, $this->startPosition->column
		);
	}

	public function jsonSerialize(): string {
		return sprintf("%s(%d:%d:%d-%d:%d:%d)",
			$this->moduleName,
			$this->startPosition->line, $this->startPosition->column, $this->startPosition->offset,
			$this->endPosition->line, $this->endPosition->column, $this->endPosition->offset
		);
	}
}