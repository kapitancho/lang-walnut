<?php

namespace Walnut\Lang\Implementation\AST\Node\Type;

use BcMath\Number;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\RealTypeNode as RealTypeNodeInterface;
use Walnut\Lang\Blueprint\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Range\PlusInfinity;

final readonly class RealTypeNode implements RealTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public Number|MinusInfinity $minValue,
		public Number|PlusInfinity $maxValue
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'RealType',
			'minValue' => $this->minValue,
			'maxValue' => $this->maxValue
		];
	}
}