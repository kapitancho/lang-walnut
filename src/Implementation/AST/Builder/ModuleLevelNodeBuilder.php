<?php

namespace Walnut\Lang\Implementation\AST\Builder;

use Walnut\Lang\Blueprint\AST\Builder\ModuleLevelNodeBuilder as ModuleLevelNodeBuilderInterface;
use Walnut\Lang\Blueprint\AST\Builder\SourceLocator;
use Walnut\Lang\Blueprint\AST\Node\FunctionBodyNode as FunctionBodyNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Module\ModuleDefinitionNode;
use Walnut\Lang\Blueprint\AST\Node\NameAndTypeNode as NameAndTypeNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation as SourceLocationInterface;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Implementation\AST\Node\Module\AddAliasTypeNode;
use Walnut\Lang\Implementation\AST\Node\Module\AddAtomTypeNode;
use Walnut\Lang\Implementation\AST\Node\Module\AddConstructorMethodNode;
use Walnut\Lang\Implementation\AST\Node\Module\AddDataTypeNode;
use Walnut\Lang\Implementation\AST\Node\Module\AddEnumerationTypeNode;
use Walnut\Lang\Implementation\AST\Node\Module\AddMethodNode;
use Walnut\Lang\Implementation\AST\Node\Module\AddOpenTypeNode;
use Walnut\Lang\Implementation\AST\Node\Module\AddSealedTypeNode;
use Walnut\Lang\Implementation\AST\Node\Module\ModuleNode;


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
		TypeNode $targetType,
		MethodNameIdentifier $methodName,
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
		TypeNameIdentifier $typeName,
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

	public function addAtom(TypeNameIdentifier $name): AddAtomTypeNode {
		return new AddAtomTypeNode($this->getSourceLocation(), $name);
	}

	/** @param list<EnumValueIdentifier> $values */
	public function addEnumeration(TypeNameIdentifier $name, array $values): AddEnumerationTypeNode {
		return new AddEnumerationTypeNode($this->getSourceLocation(), $name, $values);
	}

	public function addAlias(TypeNameIdentifier $name, TypeNode $aliasedType): AddAliasTypeNode {
		return new AddAliasTypeNode($this->getSourceLocation(), $name, $aliasedType);
	}

	public function addData(
		TypeNameIdentifier $name,
		TypeNode $valueType,
	): AddDataTypeNode {
		return new AddDataTypeNode(
			$this->getSourceLocation(),
			$name,
			$valueType,
		);
	}

	public function addOpen(
		TypeNameIdentifier        $name,
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
		TypeNameIdentifier $name,
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