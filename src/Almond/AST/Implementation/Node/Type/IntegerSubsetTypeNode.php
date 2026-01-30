<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Type;

use BcMath\Number;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\IntegerSubsetTypeNode as IntegerSubsetTypeNodeInterface;

final readonly class IntegerSubsetTypeNode implements IntegerSubsetTypeNodeInterface {
	/** @param list<Number> $values */
	public function __construct(
		public SourceLocation $sourceLocation,
		public array $values
	) {}

	public function children(): iterable {
		return [];
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'IntegerSubsetType',
			'values' => $this->values
		];
	}
}