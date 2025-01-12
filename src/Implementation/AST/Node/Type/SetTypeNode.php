<?php

namespace Walnut\Lang\Implementation\AST\Node\Type;

use BcMath\Number;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\SetTypeNode as SetTypeNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;

final readonly class SetTypeNode implements SetTypeNodeInterface {
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
			'nodeName' => 'SetType',
			'itemType' => $this->itemType,
			'minLength' => $this->minLength,
			'maxLength' => $this->maxLength
		];
	}
}