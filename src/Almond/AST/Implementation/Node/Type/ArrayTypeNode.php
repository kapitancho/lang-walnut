<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Type;

use BcMath\Number;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\ArrayTypeNode as ArrayTypeNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Number\PlusInfinity;

final readonly class ArrayTypeNode implements ArrayTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNode $itemType,
		public Number $minLength,
		public Number|PlusInfinity $maxLength
	) {}

	public function children(): iterable {
		yield $this->itemType;
	}

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