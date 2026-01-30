<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Type;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\IntegerFullTypeNode as IntegerFullTypeNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\NumberIntervalNode;

final readonly class IntegerFullTypeNode implements IntegerFullTypeNodeInterface {
	/** @param NumberIntervalNode[] $intervals */
	public function __construct(
		public SourceLocation $sourceLocation,
		public array $intervals
	) {}

	public function children(): iterable {
		yield from $this->intervals;
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'IntegerFullType',
			'intervals' => $this->intervals
		];
	}
}