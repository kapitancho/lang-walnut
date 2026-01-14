<?php

namespace Walnut\Lang\Blueprint\AST\Builder;

use Walnut\Lang\Blueprint\AST\Node\FunctionBodyNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddAliasTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddAtomTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddConstructorMethodNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddDataTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddEnumerationTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddMethodNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddOpenTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddSealedTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Module\ModuleDefinitionNode;
use Walnut\Lang\Blueprint\AST\Node\Module\ModuleNode;
use Walnut\Lang\Blueprint\AST\Node\NameAndTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;

interface ModuleLevelNodeBuilder {
	public function moduleName(string $moduleName): self;
	/** @param list<string> $dependencies */
	public function moduleDependencies(array $dependencies): self;
	public function definition(ModuleDefinitionNode $definition): self;

	public function build(): ModuleNode;

	public function addMethod(
		TypeNode $targetType,
		MethodNameIdentifier $methodName,
		NameAndTypeNode $parameter,
		NameAndTypeNode $dependency,
		TypeNode $returnType,
		FunctionBodyNode $functionBody,
	): AddMethodNode;

	public function addConstructorMethod(
		TypeNameIdentifier $typeName,
		NameAndTypeNode $parameter,
		NameAndTypeNode $dependency,
		TypeNode $errorType,
		FunctionBodyNode $functionBody,
	): AddConstructorMethodNode;

	public function addAtom(TypeNameIdentifier $name): AddAtomTypeNode;

	/** @param list<EnumValueIdentifier> $values */
	public function addEnumeration(TypeNameIdentifier $name, array $values): AddEnumerationTypeNode;

	public function addAlias(TypeNameIdentifier $name, TypeNode $aliasedType): AddAliasTypeNode;

	public function addData(
		TypeNameIdentifier $name,
		TypeNode $valueType,
	): AddDataTypeNode;

	public function addOpen(
		TypeNameIdentifier $name,
		TypeNode $valueType,
		FunctionBodyNode|null $constructorBody,
		TypeNode|null $errorType
	): AddOpenTypeNode;

	public function addSealed(
		TypeNameIdentifier $name,
		TypeNode $valueType,
		FunctionBodyNode|null $constructorBody,
		TypeNode|null $errorType
	): AddSealedTypeNode;
}