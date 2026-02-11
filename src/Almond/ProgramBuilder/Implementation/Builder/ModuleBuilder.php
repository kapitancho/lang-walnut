<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Builder;

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
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\FunctionBody;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\NameAndType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\UserlandFunction;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland\UserlandMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland\UserlandMethodBuilder;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\EnumerationType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\DuplicateSubsetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\UnknownType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\EnumerationValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramContext;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder\FunctionBodyBuilder as FunctionBodyCompilerInterface;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder\ModuleBuilder as ModuleCompilerInterface;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder\NameBuilder;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder\TypeBuilder as TypeCompilerInterface;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\BuildException;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\CodeMapper;

final readonly class ModuleBuilder implements ModuleCompilerInterface {
	public function __construct(
		private NameBuilder        $nameBuilder,
		private ProgramContext                $programContext,
		private TypeCompilerInterface         $typeCompiler,
		private FunctionBodyCompilerInterface $functionBodyCompiler,
		private CodeMapper                    $codeMapper,
		private UserlandMethodBuilder         $userlandMethodBuilder,
	) {}

	/** @throws BuildException */
	public function compileModule(ModuleNode $module): void {
		array_map(function(ModuleDefinitionNode $moduleDefinition) {
			$this->compileModuleDefinition($moduleDefinition);
		}, $module->definitions);
	}

	/** @throws BuildException */
	public function addConstructorMethod(
		AddConstructorMethodNode $moduleDefinition
	): UserlandMethod {
		$typeName = $moduleDefinition->typeName;
		$parameterType = $this->type($moduleDefinition->parameter->type);
		$parameterName = $moduleDefinition->parameter->name;
		$dependencyType = $this->type($moduleDefinition->dependency->type);
		$dependencyName = $moduleDefinition->dependency->name;
		$errorType = $this->type($moduleDefinition->errorType);
		$functionBody = $this->functionBody($moduleDefinition->functionBody);

		try {
			$type = $this->programContext->typeRegistry->typeByName(
				$this->nameBuilder->typeName($typeName)
			);
		} catch (UnknownType $e) {
			throw new BuildException(
				$moduleDefinition,
				$e->getMessage(),
				$e
			);
		}
		$returnType = match(true) {
			$type instanceof OpenType, $type instanceof SealedType => $type->valueType,
			$type instanceof EnumerationType => $this->programContext->typeRegistry->union([
				$type,
				$this->programContext->typeRegistry->stringSubset(
					array_map(fn(EnumerationValue $enumValue): string
						=> $enumValue->name, $type->values
					)
				)
			]),
			// @codeCoverageIgnoreStart
			default => throw new BuildException(
				$moduleDefinition,
				"Constructors are only allowed for open and sealed types and for enumerations.",
			)
			// @codeCoverageIgnoreEnd
		};
		return $this->userlandMethodBuilder->addMethod(
			$this->nameBuilder->typeName('Constructor'),
			$this->nameBuilder->methodName($typeName->name),
			new NameAndType(
				$parameterType,
				$parameterName ? $this->nameBuilder->variableName($parameterName) : null,
			),
			new NameAndType(
				$dependencyType,
				$dependencyName ? $this->nameBuilder->variableName($dependencyName) : null,
			),
			$errorType instanceof NothingType ? $returnType : $this->programContext->typeRegistry->result(
				$returnType, $errorType
			),
			$functionBody,
		);
	}

	private function validator(
		TypeName $typeName,
		Type $valueType,
		TypeNode|null $errorTypeNode,
		FunctionBodyNode $constructorBodyNode
	): UserlandFunction {
		$errorType = $errorTypeNode ? $this->type($errorTypeNode) : $this->programContext->typeRegistry->nothing;
		$constructorBody = $this->functionBody($constructorBodyNode);
		return $this->programContext->userlandMethodStorage->addValidator(
			$typeName,
			$this->programContext->userlandFunctionFactory->create(
				new NameAndType($this->programContext->typeRegistry->nothing, null),
				new NameAndType($valueType, $typeName->asVariableName()),
				new NameAndType($this->programContext->typeRegistry->nothing, null),
				$errorType instanceof NothingType ? $valueType :
					$this->programContext->typeRegistry->result(
						$valueType,
						$errorType
					),
				$this->programContext->expressionRegistry->functionBody(
					$this->programContext->expressionRegistry->sequence([
						$constructorBody->expression,
						$this->programContext->expressionRegistry->variableName(
							$this->nameBuilder->variableName('#')
						)
					])
				)
			)
		);
	}

	/** @throws BuildException */
	private function compileModuleDefinition(ModuleDefinitionNode $moduleDefinition): void {
		try {
			$result = match(true) {
				$moduleDefinition instanceof AddAliasTypeNode =>
					$this->programContext->userlandTypeBuilder->addAlias(
						$this->nameBuilder->typeName($moduleDefinition->name),
						$this->type($moduleDefinition->aliasedType)
					),
				$moduleDefinition instanceof AddAtomTypeNode =>
					$this->programContext->userlandTypeBuilder->addAtom(
						$this->nameBuilder->typeName($moduleDefinition->name)),
				$moduleDefinition instanceof AddConstructorMethodNode =>
					$this->addConstructorMethod($moduleDefinition),
				$moduleDefinition instanceof AddEnumerationTypeNode =>
					$this->programContext->userlandTypeBuilder->addEnumeration(
						$this->nameBuilder->typeName($moduleDefinition->name),
						array_map(fn(EnumerationValueNameNode $enumValue): EnumerationValueName =>
							$this->nameBuilder->enumerationValueName($enumValue),
							$moduleDefinition->values
						)
					),
				$moduleDefinition instanceof AddMethodNode =>
					$this->programContext->userlandMethodBuilder->addMethod(
						$this->nameBuilder->typeName($moduleDefinition->targetType),
						$this->nameBuilder->methodName($moduleDefinition->methodName),
						new NameAndType(
							$this->type($moduleDefinition->parameter->type),
							$moduleDefinition->parameter->name ?
								$this->nameBuilder->variableName($moduleDefinition->parameter->name) : null,
						),
						new NameAndType(
							$this->type($moduleDefinition->dependency->type),
							$moduleDefinition->dependency->name ?
								$this->nameBuilder->variableName($moduleDefinition->dependency->name) : null,
						),
						$this->type($moduleDefinition->returnType),
						$this->functionBody($moduleDefinition->functionBody),
					),
				$moduleDefinition instanceof AddDataTypeNode =>
					$this->programContext->userlandTypeBuilder->addData(
						$this->nameBuilder->typeName($moduleDefinition->name),
						$this->type($moduleDefinition->valueType),
					),
				$moduleDefinition instanceof AddOpenTypeNode =>
					$this->programContext->userlandTypeBuilder->addOpen(
						$typeName = $this->nameBuilder->typeName($moduleDefinition->name),
						$valueType = $this->type($moduleDefinition->valueType),
						$moduleDefinition->constructorBody ?
							$this->validator(
								$typeName,
								$valueType,
								$moduleDefinition->errorType,
								$moduleDefinition->constructorBody
							) :
							null,
					),
				$moduleDefinition instanceof AddSealedTypeNode =>
					$this->programContext->userlandTypeBuilder->addSealed(
						$typeName = $this->nameBuilder->typeName($moduleDefinition->name),
						$valueType = $this->type($moduleDefinition->valueType),
						$moduleDefinition->constructorBody ?
							$this->validator(
								$typeName,
								$valueType,
								$moduleDefinition->errorType,
								$moduleDefinition->constructorBody
							) :
							null,
					),
				// @codeCoverageIgnoreStart
				true => throw new BuildException(
					$moduleDefinition,
					"Unknown module definition node type: " . get_class($moduleDefinition)
				)
				// @codeCoverageIgnoreEnd
			};
			if ($result instanceof UserlandMethod) {
				$this->codeMapper->mapNode(
					$moduleDefinition,
					$result
				);
			}
		} catch (DuplicateSubsetValue $e) {
			throw new BuildException(
				$moduleDefinition,
				$e->getMessage()
			);
		}
	}

	/** @throws BuildException */
	private function type(TypeNode $typeNode): Type {
		return $this->typeCompiler->type($typeNode);
	}

	/** @throws BuildException */
	private function functionBody(FunctionBodyNode $functionBodyNode): FunctionBody {
		return $this->functionBodyCompiler->functionBody($functionBodyNode);
	}

}