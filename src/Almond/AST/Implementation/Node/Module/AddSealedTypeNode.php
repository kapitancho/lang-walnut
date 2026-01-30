<?php

namespace Walnut\Lang\Almond\AST\Implementation\Node\Module;

use Walnut\Lang\Almond\AST\Blueprint\Node\FunctionBodyNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Module\AddSealedTypeNode as AddSealedTypeNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\TypeNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;

final readonly class AddSealedTypeNode implements AddSealedTypeNodeInterface {
	public function __construct(
		public SourceLocation $sourceLocation,
		public TypeNameNode $name,
		public TypeNode $valueType,
		public FunctionBodyNode|null $constructorBody,
		public TypeNode|null $errorType,
	) {}

	public function children(): iterable {
		yield $this->name;
		yield $this->valueType;
		if ($this->constructorBody !== null) {
			yield $this->constructorBody;
		}
		if ($this->errorType !== null) {
			yield $this->errorType;
		}
	}

	public function jsonSerialize(): array {
		return [
			'sourceLocation' => $this->sourceLocation,
			'nodeCategory' => 'ModuleDefinition',
			'nodeName' => 'AddSealedType',
			'name' => $this->name,
			'valueType' => $this->valueType,
			'constructorBody' => $this->constructorBody,
			'errorType' => $this->errorType
		];
	}
}