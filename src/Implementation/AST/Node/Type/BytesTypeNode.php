<?php

namespace Walnut\Lang\Implementation\AST\Node\Type;

use BcMath\Number;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\BytesTypeNode as BytesTypeNodeInterface;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;

final readonly class BytesTypeNode implements BytesTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public Number $minLength,
		public Number|PlusInfinity $maxLength
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'BytesType',
			'minLength' => $this->minLength,
			'maxLength' => $this->maxLength
		];
	}
}