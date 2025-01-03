<?php

namespace Walnut\Lang\Implementation\AST\Node\Type;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\Type\EnumerationSubsetTypeNode as EnumerationSubsetTypeNodeInterface;
use Walnut\Lang\Blueprint\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;

final readonly class EnumerationSubsetTypeNode implements EnumerationSubsetTypeNodeInterface {
	/** @param list<EnumValueIdentifier> $values */
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNameIdentifier $name,
		public array $values,
	) {}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'Type',
			'nodeName' => 'EnumerationSubsetType',
			'name' => $this->name,
			'values' => $this->values
		];
	}
}