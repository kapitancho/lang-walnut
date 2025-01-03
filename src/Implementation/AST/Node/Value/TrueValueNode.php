<?php

namespace Walnut\Lang\Implementation\AST\Node\Value;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Value\TrueValueNode as TrueValueNodeInterface;

final readonly class TrueValueNode implements TrueValueNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Value',
			'nodeName' => 'TrueValue'
		];
	}
}