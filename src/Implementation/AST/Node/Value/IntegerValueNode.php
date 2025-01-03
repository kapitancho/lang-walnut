<?php

namespace Walnut\Lang\Implementation\AST\Node\Value;

use BcMath\Number;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Value\IntegerValueNode as IntegerValueNodeInterface;

final readonly class IntegerValueNode implements IntegerValueNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public Number $value
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Value',
			'nodeName' => 'IntegerValue',
			'value' => $this->value
		];
	}
}