<?php

namespace Walnut\Lang\Implementation\AST\Node\Type;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\ImpureTypeNode as ImpureTypeNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;

final readonly class ImpureTypeNode implements ImpureTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNode $valueType
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'ImpureType',
			'valueType' => $this->valueType
		];
	}
}