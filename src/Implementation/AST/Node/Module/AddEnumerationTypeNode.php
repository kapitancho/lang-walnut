<?php

namespace Walnut\Lang\Implementation\AST\Node\Module;

use Walnut\Lang\Blueprint\AST\Node\Module\AddEnumerationTypeNode as AddEnumerationTypeNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;

final readonly class AddEnumerationTypeNode implements AddEnumerationTypeNodeInterface {
	/** @param list<EnumValueIdentifier> $values */
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNameIdentifier $name,
		public array $values,
	) {}

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