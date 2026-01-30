<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Type;

use BcMath\Number;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\StringTypeNode as StringTypeNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Number\PlusInfinity;

final readonly class StringTypeNode implements StringTypeNodeInterface {
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
			'nodeName' => 'StringType',
			'minLength' => $this->minLength,
			'maxLength' => $this->maxLength
		];
	}
}