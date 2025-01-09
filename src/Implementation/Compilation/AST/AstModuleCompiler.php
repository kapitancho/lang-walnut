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
use Walnut\Lang\Blueprint\Compilation\AST\AstExpressionCompiler;
use Walnut\Lang\Blueprint\Compilation\AST\AstFunctionBodyCompiler;
use Walnut\Lang\Blueprint\Compilation\AST\AstModuleCompilationException;
use Walnut\Lang\Blueprint\Compilation\AST\AstModuleCompiler as AstModuleCompilerInterface;
use Walnut\Lang\Blueprint\Compilation\AST\AstTypeCompiler;
use Walnut\Lang\Blueprint\Compilation\AST\AstValueCompiler;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Blueprint\Program\Builder\ProgramTypeBuilder;
use Walnut\Lang\Blueprint\Program\Builder\ScopeBuilder;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Blueprint\Value\Value;

final readonly class AstModuleCompiler implements AstModuleCompilerInterface {
	public function __construct(
		private ProgramTypeBuilder          $programTypeBuilder,
		private AstTypeCompiler             $astTypeCompiler,
		private AstValueCompiler            $astValueCompiler,
		private AstExpressionCompiler       $astExpressionCompiler,
		private AstFunctionBodyCompiler     $astFunctionBodyCompiler,
		private ScopeBuilder                $globalScopeBuilder
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

	/** @throws AstCompilationException */
	private function compileModuleDefinition(ModuleDefinitionNode $moduleDefinition): void {
		match(true) {
			$moduleDefinition instanceof AddAliasTypeNode =>
				$this->programTypeBuilder->addAlias(
					$moduleDefinition->name,
					$this->type($moduleDefinition->aliasedType)
				),
			$moduleDefinition instanceof AddAtomTypeNode =>
				$this->programTypeBuilder->addAtom($moduleDefinition->name),
			$moduleDefinition instanceof AddConstructorMethodNode =>
				$this->programTypeBuilder->customMethodRegistryBuilder->addConstructorMethod(
					$moduleDefinition->typeName,
					$this->type($moduleDefinition->parameterType),
					$this->type($moduleDefinition->dependencyType),
					$this->type($moduleDefinition->errorType),
					$this->functionBody($moduleDefinition->functionBody)
				),
			$moduleDefinition instanceof AddEnumerationTypeNode =>
				$this->programTypeBuilder->addEnumeration(
					$moduleDefinition->name,
					$moduleDefinition->values
				),
			$moduleDefinition instanceof AddMethodNode =>
				$this->programTypeBuilder->customMethodRegistryBuilder->addMethod(
					$this->type($moduleDefinition->targetType),
					$moduleDefinition->methodName,
					$this->type($moduleDefinition->parameterType),
					$this->type($moduleDefinition->dependencyType),
					$this->type($moduleDefinition->returnType),
					$this->functionBody($moduleDefinition->functionBody)
				),
			$moduleDefinition instanceof AddSealedTypeNode =>
				$this->programTypeBuilder->addSealed(
					$moduleDefinition->name,
					$this->type($moduleDefinition->valueType),
					$this->astExpressionCompiler->expression($moduleDefinition->constructorBody),
					$this->type($moduleDefinition->errorType)
				),
			$moduleDefinition instanceof AddSubtypeTypeNode =>
				$this->programTypeBuilder->addSubtype(
					$moduleDefinition->name,
					$this->type($moduleDefinition->baseType),
					$this->astExpressionCompiler->expression($moduleDefinition->constructorBody),
					$this->type($moduleDefinition->errorType)
				),
			$moduleDefinition instanceof AddVariableNode =>
				$this->globalScopeBuilder->addVariable($moduleDefinition->name,
					$moduleDefinition->value instanceof FunctionValueNode ?
						$this->globalFunction(
							new MethodNameIdentifier($moduleDefinition->name->identifier),
							$moduleDefinition->value
						) :
						$this->value($moduleDefinition->value)),

			true => throw new AstCompilationException(
				$moduleDefinition,
				"Unknown module definition node type: " . get_class($moduleDefinition)
			)
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
		$this->programTypeBuilder->customMethodRegistryBuilder->addMethod(
			$this->programTypeBuilder->typeRegistry->typeByName(
				new TypeNameIdentifier('Global')
			),
			$methodName,
			$parameterType = $this->type($functionValueNode->parameterType),
			$this->type($functionValueNode->dependencyType),
			$returnType = $this->type($functionValueNode->returnType),
			$this->functionBody($functionValueNode->functionBody)
		);
		return $this->programTypeBuilder->valueRegistry->function(
			$parameterType,
			$this->programTypeBuilder->typeRegistry->typeByName(
				new TypeNameIdentifier('Global')
			),
			$returnType,
			$this->programTypeBuilder->expressionRegistry->functionBody(
				$this->programTypeBuilder->expressionRegistry->methodCall(
					$this->programTypeBuilder->expressionRegistry->variableName(new VariableNameIdentifier('%')),
					$methodName,
					$this->programTypeBuilder->expressionRegistry->variableName(new VariableNameIdentifier('#')),
				)
			)
		);
	}

}