<?php

namespace Walnut\Lang\Implementation\AST\Node\Type;

use BcMath\Number;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\IntegerSubsetTypeNode as IntegerSubsetTypeNodeInterface;

final readonly class IntegerSubsetTypeNode implements IntegerSubsetTypeNodeInterface {
	/** @param list<Number> $values */
	public function __construct(
		public SourceLocation $sourceLocation,
		public array $values
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'IntegerSubsetType',
			'values' => $this->values
		];
	}
}