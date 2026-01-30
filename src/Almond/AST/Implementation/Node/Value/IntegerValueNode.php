<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Value;

use BcMath\Number;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\IntegerValueNode as IntegerValueNodeInterface;

final readonly class IntegerValueNode implements IntegerValueNodeInterface {
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
			'nodeName' => 'IntegerValue',
			'value' => $this->value
		];
	}
}