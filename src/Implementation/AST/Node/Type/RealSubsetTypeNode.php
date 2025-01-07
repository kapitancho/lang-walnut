<?php

namespace Walnut\Lang\Implementation\AST\Node\Type;

use BcMath\Number;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\RealSubsetTypeNode as RealSubsetTypeNodeInterface;

final readonly class RealSubsetTypeNode implements RealSubsetTypeNodeInterface {
	/** @param list<Number> $values */
	public function __construct(
		public SourceLocation $sourceLocation,
		public array $values
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'RealSubsetType',
			'values' => $this->values
		];
	}
}