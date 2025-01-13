<?php

namespace Walnut\Lang\Implementation\Compilation\AST;

use Walnut\Lang\Blueprint\AST\Node\FunctionBodyNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddAliasTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddAtomTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddConstructorMethodNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddEnumerationTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddMethodNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddSealedTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddSubtypeTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddVariableNode;
use Walnut\Lang\Blueprint\AST\Node\Module\ModuleDefinitionNode;
use Walnut\Lang\Blueprint\AST\Node\Module\ModuleNode;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\AST\Node\Value\FunctionValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\ValueNode;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Compilation\AST\AstCompilationException;
use Walnut\Lang\Blueprint\Compilation\AST\AstFunctionBodyCompiler;
use Walnut\Lang\Blueprint\Compilation\AST\AstModuleCompilationException;
use Walnut\Lang\Blueprint\Compilation\AST\AstModuleCompiler as AstModuleCompilerInterface;
use Walnut\Lang\Blueprint\Compilation\AST\AstTypeCompiler;
use Walnut\Lang\Blueprint\Compilation\AST\AstValueCompiler;
use Walnut\Lang\Blueprint\Compilation\CompilationException;
use Walnut\Lang\Blueprint\Function\CustomMethod;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Blueprint\Program\ProgramContext;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\SubtypeType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Blueprint\Value\Value;

final readonly class AstModuleCompiler implements AstModuleCompilerInterface {
	public function __construct(
		private ProgramContext          $programContext,
		private AstTypeCompiler         $astTypeCompiler,
		private AstValueCompiler        $astValueCompiler,
		private AstFunctionBodyCompiler $astFunctionBodyCompiler,
	) {}

	/** @throws AstModuleCompilationException */
	public function compileModule(ModuleNode $module): void {
		$exceptions = array();
		array_map(function(ModuleDefinitionNode $moduleDefinition) use (&$exceptions) {
			try {
				$this->compileModuleDefinition($moduleDefinition);
			} catch (AstCompilationException $e) {
				$exceptions[] = $e;
			}
		}, $module->definitions);

		if (count($exceptions) > 0) {
			throw new AstModuleCompilationException($module->moduleName, $exceptions);
		}
	}

	/** @throws CompilationException */
	public function addConstructorMethod(
		TypeNameIdentifier $typeName,
		Type $parameterType,
		Type $dependencyType,
		Type $errorType,
		FunctionBody $functionBody
	): CustomMethod {
		$type = $this->programContext->typeRegistry->typeByName($typeName);
		$returnType = match(true) {
			$type instanceof SealedType => $type->valueType,
			$type instanceof SubtypeType => $type->baseType,
			// @codeCoverageIgnoreStart
			default => throw new CompilationException(
				"Constructors are only allowed for subtypes and sealed types",
			)
			// @codeCoverageIgnoreEnd
		};
		return $this->programContext->customMethodRegistryBuilder->addMethod(
			$this->programContext->typeRegistry->typeByName(new TypeNameIdentifier('Constructor')),
			new MethodNameIdentifier($typeName),
			$parameterType,
			$dependencyType,
			$errorType instanceof NothingType ? $returnType : $this->programContext->typeRegistry->result(
				$returnType, $errorType
			),
			$functionBody
		);
	}


	/** @throws AstCompilationException */
	private function compileModuleDefinition(ModuleDefinitionNode $moduleDefinition): void {
		match(true) {
			$moduleDefinition instanceof AddAliasTypeNode =>
				$this->programContext->typeRegistryBuilder->addAlias(
					$moduleDefinition->name,
					$this->type($moduleDefinition->aliasedType)
				),
			$moduleDefinition instanceof AddAtomTypeNode =>
				$this->programContext->typeRegistryBuilder->addAtom($moduleDefinition->name),
			$moduleDefinition instanceof AddConstructorMethodNode =>
				$this->addConstructorMethod(
					$moduleDefinition->typeName,
					$this->type($moduleDefinition->parameterType),
					$this->type($moduleDefinition->dependencyType),
					$this->type($moduleDefinition->errorType),
					$this->functionBody($moduleDefinition->functionBody)
				),
			$moduleDefinition instanceof AddEnumerationTypeNode =>
				$this->programContext->typeRegistryBuilder->addEnumeration(
					$moduleDefinition->name,
					$moduleDefinition->values
				),
			$moduleDefinition instanceof AddMethodNode =>
				$this->programContext->customMethodRegistryBuilder->addMethod(
					$this->type($moduleDefinition->targetType),
					$moduleDefinition->methodName,
					$this->type($moduleDefinition->parameterType),
					$this->type($moduleDefinition->dependencyType),
					$this->type($moduleDefinition->returnType),
					$this->functionBody($moduleDefinition->functionBody)
				),
			$moduleDefinition instanceof AddSealedTypeNode =>
				$this->programContext->typeRegistryBuilder->addSealed(
					$moduleDefinition->name,
					$this->type($moduleDefinition->valueType),
					$this->functionBody(
						$moduleDefinition->constructorBody
					),
					$this->type($moduleDefinition->errorType)
				),
			$moduleDefinition instanceof AddSubtypeTypeNode =>
				$this->programContext->typeRegistryBuilder->addSubtype(
					$moduleDefinition->name,
					$this->type($moduleDefinition->baseType),
					$this->functionBody(
						$moduleDefinition->constructorBody
					),
					$this->type($moduleDefinition->errorType)
				),
			$moduleDefinition instanceof AddVariableNode =>
				$this->programContext->globalScopeBuilder->addVariable($moduleDefinition->name,
					$moduleDefinition->value instanceof FunctionValueNode ?
						$this->globalFunction(
							new MethodNameIdentifier($moduleDefinition->name->identifier),
							$moduleDefinition->value
						) :
						$this->value($moduleDefinition->value)),

			// @codeCoverageIgnoreStart
			true => throw new AstCompilationException(
				$moduleDefinition,
				"Unknown module definition node type: " . get_class($moduleDefinition)
			)
			// @codeCoverageIgnoreEnd
		};
	}

	/** @throws AstCompilationException */
	private function type(TypeNode $typeNode): Type {
		return $this->astTypeCompiler->type($typeNode);
	}

	/** @throws AstCompilationException */
	private function value(ValueNode $valueNode): Value {
		return $this->astValueCompiler->value($valueNode);
	}


	/** @throws AstCompilationException */
	private function functionBody(FunctionBodyNode $functionBodyNode): FunctionBody {
		return $this->astFunctionBodyCompiler->functionBody($functionBodyNode);
	}

	private function globalFunction(MethodNameIdentifier $methodName, FunctionValueNode $functionValueNode): FunctionValue {
		$this->programContext->customMethodRegistryBuilder->addMethod(
			$this->programContext->typeRegistry->typeByName(
				new TypeNameIdentifier('Global')
			),
			$methodName,
			$parameterType = $this->type($functionValueNode->parameterType),
			$this->type($functionValueNode->dependencyType),
			$returnType = $this->type($functionValueNode->returnType),
			$this->functionBody($functionValueNode->functionBody)
		);
		return $this->programContext->valueRegistry->function(
			$parameterType,
			$this->programContext->typeRegistry->typeByName(
				new TypeNameIdentifier('Global')
			),
			$returnType,
			$this->programContext->expressionRegistry->functionBody(
				$this->programContext->expressionRegistry->methodCall(
					$this->programContext->expressionRegistry->variableName(new VariableNameIdentifier('%')),
					$methodName,
					$this->programContext->expressionRegistry->variableName(new VariableNameIdentifier('#')),
				)
			)
		);
	}

}