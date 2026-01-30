<?php

namespace Walnut\Lang\Almond\AST\Implementation\Builder;

use Walnut\Lang\Almond\AST\Blueprint\Builder\ModuleLevelNodeBuilder as ModuleLevelNodeBuilderInterface;
use Walnut\Lang\Almond\AST\Blueprint\Builder\SourceLocator;
use Walnut\Lang\Almond\AST\Blueprint\Node\FunctionBodyNode as FunctionBodyNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Module\ModuleDefinitionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\EnumerationValueNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\MethodNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\TypeNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\NameAndTypeNode as NameAndTypeNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation as SourceLocationInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Module\AddAliasTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Module\AddAtomTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Module\AddConstructorMethodNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Module\AddDataTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Module\AddEnumerationTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Module\AddMethodNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Module\AddOpenTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Module\AddSealedTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Module\ModuleNode;

final class ModuleLevelNodeBuilder implements ModuleLevelNodeBuilderInterface {
	private string $moduleName;
	/** @param list<string> $moduleDependencies */
	private array $moduleDependencies = [];
	/** @param list<ModuleDefinitionNode> $definitions */
	private array $definitions = [];

	public function __construct(
		private readonly SourceLocator $sourceLocator
	) {
		$this->moduleName = $this->sourceLocator->getSourceLocation()->moduleName;
	}

	public function moduleName(string $moduleName): self {
		$this->moduleName = $moduleName;
		return $this;
	}

	public function moduleDependencies(array $dependencies): self {
		$this->moduleDependencies = $dependencies;
		return $this;
	}

	public function definition(ModuleDefinitionNode $definition): self {
		$this->definitions[] = $definition;
		return $this;
	}

	public function build(): ModuleNode {
		return new ModuleNode(
			$this->moduleName,
			$this->moduleDependencies,
			$this->definitions
		);
	}

	private function getSourceLocation(): SourceLocationInterface {
		return $this->sourceLocator->getSourceLocation();
	}

	public function addMethod(
		TypeNameNode $targetType,
		MethodNameNode $methodName,
		NameAndTypeNodeInterface $parameter,
		NameAndTypeNodeInterface $dependency,
		TypeNode $returnType,
		FunctionBodyNodeInterface $functionBody
	): AddMethodNode {
		return new AddMethodNode(
			$this->getSourceLocation(),
			$targetType,
			$methodName,
			$parameter,
			$dependency,
			$returnType,
			$functionBody
		);
	}

	public function addConstructorMethod(
		TypeNameNode $typeName,
		NameAndTypeNodeInterface $parameter,
		NameAndTypeNodeInterface $dependency,
		TypeNode $errorType,
		FunctionBodyNodeInterface $functionBody
	): AddConstructorMethodNode {
		return new AddConstructorMethodNode(
			$this->getSourceLocation(),
			$typeName,
			$parameter,
			$dependency,
			$errorType,
			$functionBody
		);
	}

	public function addAtom(TypeNameNode $name): AddAtomTypeNode {
		return new AddAtomTypeNode($this->getSourceLocation(), $name);
	}

	/** @param list<EnumerationValueNameNode> $values */
	public function addEnumeration(TypeNameNode $name, array $values): AddEnumerationTypeNode {
		return new AddEnumerationTypeNode($this->getSourceLocation(), $name, $values);
	}

	public function addAlias(TypeNameNode $name, TypeNode $aliasedType): AddAliasTypeNode {
		return new AddAliasTypeNode($this->getSourceLocation(), $name, $aliasedType);
	}

	public function addData(
		TypeNameNode $name,
		TypeNode $valueType,
	): AddDataTypeNode {
		return new AddDataTypeNode(
			$this->getSourceLocation(),
			$name,
			$valueType,
		);
	}

	public function addOpen(
		TypeNameNode $name,
		TypeNode                  $valueType,
		FunctionBodyNodeInterface|null $constructorBody,
		TypeNode|null             $errorType
	): AddOpenTypeNode {
		return new AddOpenTypeNode(
			$this->getSourceLocation(),
			$name,
			$valueType,
			$constructorBody,
			$errorType
		);
	}

	public function addSealed(
		TypeNameNode $name,
		TypeNode $valueType,
		FunctionBodyNodeInterface|null $constructorBody,
		TypeNode|null $errorType
	): AddSealedTypeNode {
		return new AddSealedTypeNode(
			$this->getSourceLocation(),
			$name,
			$valueType,
			$constructorBody,
			$errorType
		);
	}

}