<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Value;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\TrueValueNode as TrueValueNodeInterface;

final readonly class TrueValueNode implements TrueValueNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation
	) {}

	public function children(): iterable {
		return [];
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Value',
			'nodeName' => 'TrueValue'
		];
	}
}