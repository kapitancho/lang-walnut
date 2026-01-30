<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Module;

use Walnut\Lang\Almond\AST\Blueprint\Node\Module\AddEnumerationTypeNode as AddEnumerationTypeNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\EnumerationValueNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\TypeNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;

final readonly class AddEnumerationTypeNode implements AddEnumerationTypeNodeInterface {
	/** @param list<EnumerationValueNameNode> $values */
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNameNode $name,
		public array $values,
	) {}

	public function children(): iterable {
		yield $this->name;
		yield from $this->values;
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'ModuleDefinition',
			'nodeName' => 'AddEnumerationType',
			'name' => $this->name,
			'values' => $this->values
		];
	}
}