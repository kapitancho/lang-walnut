<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Builder;

use Walnut\Lang\Almond\AST\Blueprint\Node\FunctionBodyNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Module\AddAliasTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Module\AddAtomTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Module\AddConstructorMethodNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Module\AddDataTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Module\AddEnumerationTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Module\AddMethodNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Module\AddOpenTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Module\AddSealedTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Module\ModuleDefinitionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Module\ModuleNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\EnumerationValueNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\MethodNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\TypeNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\NameAndTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;

interface ModuleLevelNodeBuilder {
	public function moduleName(string $moduleName): self;
	/** @param list<string> $dependencies */
	public function moduleDependencies(array $dependencies): self;
	public function definition(ModuleDefinitionNode $definition): self;

	public function build(): ModuleNode;

	public function addMethod(
		TypeNameNode $targetType,
		MethodNameNode $methodName,
		NameAndTypeNode $parameter,
		NameAndTypeNode $dependency,
		TypeNode $returnType,
		FunctionBodyNode $functionBody,
	): AddMethodNode;

	public function addConstructorMethod(
		TypeNameNode $typeName,
		NameAndTypeNode $parameter,
		NameAndTypeNode $dependency,
		TypeNode $errorType,
		FunctionBodyNode $functionBody,
	): AddConstructorMethodNode;

	public function addAtom(TypeNameNode $name): AddAtomTypeNode;

	/** @param list<EnumerationValueNameNode> $values */
	public function addEnumeration(TypeNameNode $name, array $values): AddEnumerationTypeNode;

	public function addAlias(TypeNameNode $name, TypeNode $aliasedType): AddAliasTypeNode;

	public function addData(
		TypeNameNode $name,
		TypeNode $valueType,
	): AddDataTypeNode;

	public function addOpen(
		TypeNameNode $name,
		TypeNode $valueType,
		FunctionBodyNode|null $constructorBody,
		TypeNode|null $errorType
	): AddOpenTypeNode;

	public function addSealed(
		TypeNameNode $name,
		TypeNode $valueType,
		FunctionBodyNode|null $constructorBody,
		TypeNode|null $errorType
	): AddSealedTypeNode;
}