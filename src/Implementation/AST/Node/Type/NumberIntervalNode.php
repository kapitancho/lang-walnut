<?php

namespace Walnut\Lang\Implementation\AST\Node\Type;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\NumberIntervalNode as NumberIntervalNodeInterface;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;

final readonly class NumberIntervalNode implements NumberIntervalNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public MinusInfinity|NumberIntervalEndpoint $start,
		public PlusInfinity|NumberIntervalEndpoint $end
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'NumberInterval',
			'start' => $this->start,
			'end' => $this->end
		];
	}
}