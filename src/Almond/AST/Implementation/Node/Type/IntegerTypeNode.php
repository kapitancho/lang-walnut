<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Type;

use BcMath\Number;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\IntegerTypeNode as IntegerTypeNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Number\MinusInfinity;
use Walnut\Lang\Almond\AST\Blueprint\Number\PlusInfinity;

final readonly class IntegerTypeNode implements IntegerTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public Number|MinusInfinity $minValue,
		public Number|PlusInfinity $maxValue
	) {}

	public function children(): iterable {
		return [];
	}

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