<?php

namespace Walnut\Lang\Implementation\AST\Node\Type;

use BcMath\Number;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\MapTypeNode as MapTypeNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;

final readonly class MapTypeNode implements MapTypeNodeInterface {
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
			'nodeName' => 'MapType',
			'itemType' => $this->itemType,
			'minLength' => $this->minLength,
			'maxLength' => $this->maxLength
		];
	}
}