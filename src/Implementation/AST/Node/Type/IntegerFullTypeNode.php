<?php

namespace Walnut\Lang\Implementation\AST\Node\Type;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\IntegerFullTypeNode as IntegerFullTypeNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Type\NumberIntervalNode;

final readonly class IntegerFullTypeNode implements IntegerFullTypeNodeInterface {
	/** @param NumberIntervalNode[] $intervals */
	public function __construct(
		public SourceLocation $sourceLocation,
		public array $intervals
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'IntegerFullType',
			'intervals' => $this->intervals
		];
	}
}