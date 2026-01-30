<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Type;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\NumberIntervalNode as NumberIntervalNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Number\MinusInfinity;
use Walnut\Lang\Almond\AST\Blueprint\Number\PlusInfinity;

final readonly class NumberIntervalNode implements NumberIntervalNodeInterface {
	public function __construct(
		public SourceLocation                           $sourceLocation,
		public MinusInfinity|NumberIntervalEndpointNode $start,
		public PlusInfinity|NumberIntervalEndpointNode  $end
	) {}

	public function children(): iterable {
		if ($this->start instanceof NumberIntervalEndpointNode) {
			yield $this->start;
		}
		if ($this->end instanceof NumberIntervalEndpointNode) {
			yield $this->end;
		}
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'NumberInterval',
			'start' => $this->start,
			'end' => $this->end
		];
	}
}