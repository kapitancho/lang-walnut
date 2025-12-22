<?php

namespace Walnut\Lang\Implementation\AST\Node\Type;

use BcMath\Number;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\ByteArrayTypeNode as ByteArrayTypeNodeInterface;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;

final readonly class ByteArrayTypeNode implements ByteArrayTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public Number $minLength,
		public Number|PlusInfinity $maxLength
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'ByteArrayType',
			'minLength' => $this->minLength,
			'maxLength' => $this->maxLength
		];
	}
}