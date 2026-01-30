<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Value;

use BcMath\Number;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\RealValueNode as RealValueNodeInterface;

final readonly class RealValueNode implements RealValueNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public Number $value
	) {}

	public function children(): iterable {
		return [];
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Value',
			'nodeName' => 'RealValue',
			'value' => $this->value
		];
	}
}