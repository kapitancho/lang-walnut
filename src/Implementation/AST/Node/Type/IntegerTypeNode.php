<?php

namespace Walnut\Lang\Implementation\AST\Node\Type;

use BcMath\Number;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\IntegerTypeNode as IntegerTypeNodeInterface;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;

final readonly class IntegerTypeNode implements IntegerTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public Number|MinusInfinity $minValue,
		public Number|PlusInfinity $maxValue
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'IntegerType',
			'minValue' => $this->minValue,
			'maxValue' => $this->maxValue
		];
	}
}