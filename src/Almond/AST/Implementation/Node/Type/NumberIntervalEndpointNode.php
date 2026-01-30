<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Type;

use BcMath\Number;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\NumberIntervalEndpointNode as NumberIntervalEndpointInterface;

final readonly class NumberIntervalEndpointNode implements NumberIntervalEndpointInterface {
	public function __construct(
		public Number $value,
		public bool $inclusive
	) {}

	public function children(): iterable {
		return [];
	}

	public function jsonSerialize(): array {
		return [
			'nodeCategory' => 'NumberInterval',
			'nodeName' => 'Endpoint',
			'value' => $this->value,
			'inclusive' => $this->inclusive
		];
	}
}
