<?php

namespace Walnut\Lang\Implementation\AST\Node\Value;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Value\AtomValueNode as AtomValueNodeInterface;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;

final readonly class AtomValueNode implements AtomValueNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNameIdentifier $name
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Value',
			'nodeName' => 'AtomValue',
			'name' => $this->name
		];
	}
}