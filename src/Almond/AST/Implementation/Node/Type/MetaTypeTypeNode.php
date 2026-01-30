<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Type;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\MetaTypeTypeNode as MetaTypeTypeNodeInterface;

final readonly class MetaTypeTypeNode implements MetaTypeTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public string $value
	) {}

	public function children(): iterable {
		return [];
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'MetaTypeType',
			'value' => $this->value
		];
	}
}