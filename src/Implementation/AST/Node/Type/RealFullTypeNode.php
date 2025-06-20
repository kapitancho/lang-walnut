<?php

namespace Walnut\Lang\Implementation\AST\Node\Type;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\NumberIntervalNode;
use Walnut\Lang\Blueprint\AST\Node\Type\RealFullTypeNode as RealFullTypeNodeInterface;

final readonly class RealFullTypeNode implements RealFullTypeNodeInterface {
	/** @param NumberIntervalNode[] $intervals */
	public function __construct(
		public SourceLocation $sourceLocation,
		public array $intervals
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'RealFullType',
			'intervals' => $this->intervals
		];
	}
}