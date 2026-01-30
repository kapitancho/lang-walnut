<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Type;

use BcMath\Number;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\BytesTypeNode as BytesTypeNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Number\PlusInfinity;

final readonly class BytesTypeNode implements BytesTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public Number $minLength,
		public Number|PlusInfinity $maxLength
	) {}

	public function children(): iterable {
		return [];
	}

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