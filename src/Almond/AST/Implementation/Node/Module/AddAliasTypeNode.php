<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Module;

use Walnut\Lang\Almond\AST\Blueprint\Node\Module\AddAliasTypeNode as AddAliasTypeNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\TypeNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;

final readonly class AddAliasTypeNode implements AddAliasTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNameNode $name,
		public TypeNode $aliasedType,
	) {}

	public function children(): iterable {
		yield $this->name;
		yield $this->aliasedType;
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'ModuleDefinition',
			'nodeName' => 'AddAliasType',
			'name' => $this->name,
			'aliasedType' => $this->aliasedType
		];
	}
}