<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation as SourceLocationInterface;
use Walnut\Lib\Walex\SourcePosition;

final readonly class SourceLocation implements SourceLocationInterface {
	public function __construct(
		public string $moduleName,
		public SourcePosition $startPosition,
		public SourcePosition $endPosition,
	) {}

	public function __toString(): string {
		return sprintf("module %s, starting on line %d, column %d, offset %d, ending on line %d, column %d, offset %d",
			$this->moduleName,
			$this->startPosition->line, $this->startPosition->column, $this->startPosition->offset,
			$this->endPosition->line, $this->endPosition->column, $this->endPosition->offset
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