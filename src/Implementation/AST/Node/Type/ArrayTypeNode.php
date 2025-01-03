<?php

namespace Walnut\Lang\Implementation\AST\Node\Type;

use BcMath\Number;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\ArrayTypeNode as ArrayTypeNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\Range\PlusInfinity;

final readonly class ArrayTypeNode implements ArrayTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNode $itemType,
		public Number $minLength,
		public Number|PlusInfinity $maxLength
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'ArrayType',
			'itemType' => $this->itemType,
			'minLength' => $this->minLength,
			'maxLength' => $this->maxLength
		];
	}
}