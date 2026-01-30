<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Type;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\NumberIntervalNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\RealFullTypeNode as RealFullTypeNodeInterface;

final readonly class RealFullTypeNode implements RealFullTypeNodeInterface {
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
			'nodeName' => 'RealFullType',
			'intervals' => $this->intervals
		];
	}
}