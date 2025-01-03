<?php

namespace Walnut\Lang\Implementation\AST\Node\Type;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\MetaTypeTypeNode as MetaTypeTypeNodeInterface;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;

final readonly class MetaTypeTypeNode implements MetaTypeTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public MetaTypeValue $value
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'MetaTypeType',
			'value' => $this->value->value
		];
	}
}