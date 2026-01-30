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
use Walnut\Lang\Almond\Engine\Blueprint\Function\FunctionBody;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Method\UserlandMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramContext;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland\UserlandMethodBuilder;
use Walnut\Lang\Almond\Engine\Blueprint\Type\EnumerationType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Implementation\Type\P\NameAndType;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder\FunctionBodyBuilder as FunctionBodyCompilerInterface;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder\ModuleBuilder as ModuleCompilerInterface;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder\NameBuilder;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder\TypeBuilder as TypeCompilerInterface;
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

	/** @throws ModuleCompilationException */
	public function compileModule(ModuleNode $module): void {
		$exceptions = array();
		array_map(function(ModuleDefinitionNode $moduleDefinition) use (&$exceptions) {
			try {
				$this->compileModuleDefinition($moduleDefinition);
			} catch (CompilationException $e) {
				$exceptions[] = $e;
			}
		}, $module->definitions);

		if (count($exceptions) > 0) {
			throw new ModuleCompilationException($module->moduleName, $exceptions);
		}
	}

	/** @throws CompilationException */
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
			throw new CompilationException(
				$moduleDefinition,
				$e->getMessage(),
				$e
			);
		}
		$returnType = match(true) {
			$type instanceof OpenType, $type instanceof SealedType => $type->valueType,
			$type instanceof EnumerationType => $this->programContext->typeRegistry->union([
				$type,
				$this->programContext->typeRegistry->string()
			]),
			// @codeCoverageIgnoreStart
			default => throw new CompilationException(
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


	/** @throws CompilationException */
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
						$this->nameBuilder->typeName($moduleDefinition->name),
						$this->type($moduleDefinition->valueType),
						$moduleDefinition->constructorBody ?
							$this->validatorBody(
								$moduleDefinition->constructorBody
							) : null,
						$moduleDefinition->errorType ?
							$this->type($moduleDefinition->errorType) : null
					),
				$moduleDefinition instanceof AddSealedTypeNode =>
					$this->programContext->userlandTypeBuilder->addSealed(
						$this->nameBuilder->typeName($moduleDefinition->name),
						$this->type($moduleDefinition->valueType),
						$moduleDefinition->constructorBody ?
							$this->validatorBody(
								$moduleDefinition->constructorBody
							) : null,
						$moduleDefinition->errorType ?
							$this->type($moduleDefinition->errorType) : null
					),
				// @codeCoverageIgnoreStart
				true => throw new CompilationException(
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
			throw new CompilationException(
				$moduleDefinition,
				$e->getMessage()
			);
		}
	}

	/** @throws CompilationException */
	private function type(TypeNode $typeNode): Type {
		return $this->typeCompiler->type($typeNode);
	}

	/** @throws CompilationException */
	private function functionBody(FunctionBodyNode $functionBodyNode): FunctionBody {
		return $this->functionBodyCompiler->functionBody($functionBodyNode);
	}

	/** @throws CompilationException */
	private function validatorBody(FunctionBodyNode $functionBodyNode): FunctionBody {
		return $this->functionBodyCompiler->validatorBody($functionBodyNode);
	}

}