<?php

namespace Walnut\Lang\Implementation\AST\Node\Module;

use Walnut\Lang\Blueprint\AST\Node\Module\AddAliasTypeNode as AddAliasTypeNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;

final readonly class AddAliasTypeNode implements AddAliasTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNameIdentifier $name,
		public TypeNode $aliasedType,
	) {}

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