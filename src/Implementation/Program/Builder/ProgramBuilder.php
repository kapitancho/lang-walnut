<?php

namespace Walnut\Lang\Implementation\Program\Builder;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Function\CustomMethod;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Blueprint\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Program\Builder\ProgramBuilder as ProgramBuilderInterface;
use Walnut\Lang\Blueprint\Program\Builder\TypeRegistryBuilder;
use Walnut\Lang\Blueprint\Program\Registry\ExpressionRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\AliasType;
use Walnut\Lang\Blueprint\Type\AtomType;
use Walnut\Lang\Blueprint\Type\EnumerationType;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\SubtypeType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Program\Program;

final readonly class ProgramBuilder implements ProgramBuilderInterface {

	public function __construct(
		private TypeRegistry                     $typeRegistry,
		private ExpressionRegistry               $expressionRegistry,
		private TypeRegistryBuilder              $typeRegistryBuilder,
		private CustomMethodRegistryBuilder      $customMethodRegistryBuilder,
		private ScopeBuilder                     $globalScopeBuilder,
		private AnalyserContext&ExecutionContext $globalContext,
	) {}

	/** @return string[] - the errors found during analyse */
	private function analyseGlobalFunctions(): array {
		$analyseErrors = [];

		//Part 1 - check all global functions
		foreach($this->globalScopeBuilder->build()->allTypedValues() as $name => $typedValue) {
			if ($typedValue->value instanceof FunctionValue) {
				try {
					$typedValue->value->analyse($this->globalContext);
				} catch (AnalyserException $ex) {
					$analyseErrors[] = "Error in function $name: {$ex->getMessage()}";
				}
			}
		}
		return $analyseErrors;
	}

	public function analyseAndBuildProgram(): Program {
		$globalFunctionAnalyseErrors = $this->analyseGlobalFunctions();
		$customMethodAnalyseErrors = $this->customMethodRegistryBuilder->analyse();
		$analyseErrors = [... $globalFunctionAnalyseErrors, ... $customMethodAnalyseErrors];
		if (count($analyseErrors) > 0) {
			throw new AnalyserException(implode("\n", $analyseErrors));
		}
		return new Program(
			$this->globalScopeBuilder->build(),
		);
	}

	public function addVariable(VariableNameIdentifier $name, Value $value): void {
		$this->globalScopeBuilder->addVariable($name, $value);
	}

	public function addMethod(
		Type $targetType,
		MethodNameIdentifier $methodName,
		Type $parameterType,
		Type $dependencyType,
		Type $returnType,
		FunctionBody $functionBody,
	): CustomMethod {
		return $this->customMethodRegistryBuilder->addMethod(
			$targetType,
			$methodName,
			$parameterType,
			$dependencyType,
			$returnType,
			$functionBody,
		);
	}

	public function addAtom(TypeNameIdentifier $name): AtomType {
		return $this->typeRegistryBuilder->addAtom($name);
	}

	/** @param list<EnumValueIdentifier> $values */
	public function addEnumeration(TypeNameIdentifier $name, array $values): EnumerationType {
		return $this->typeRegistryBuilder->addEnumeration($name, $values);
	}

	public function addAlias(TypeNameIdentifier $name, Type $aliasedType): AliasType {
		return $this->typeRegistryBuilder->addAlias($name, $aliasedType);
	}

	public function addSubtype(
		TypeNameIdentifier $name,
		Type $baseType,
		Expression $constructorBody,
		Type|null $errorType
	): SubtypeType {
		$subtype = $this->typeRegistryBuilder->addSubtype($name, $baseType);

		$this->addConstructorMethod($name, $baseType, $errorType, $constructorBody);

		if (0) $this->customMethodRegistryBuilder->addMethod(
			$this->typeRegistry->atom(new TypeNameIdentifier('Constructor')),
			new MethodNameIdentifier($name->identifier),
			$baseType,
			$this->typeRegistry->nothing(),
			$errorType && !($errorType instanceof NothingType) ?
				$this->typeRegistry->result($baseType /*$subtype*/, $errorType) : $baseType/*$subtype*/,
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->sequence([
					$constructorBody,
					$this->expressionRegistry->variableName(
						new VariableNameIdentifier('#')
					),
					/*$this->expressionRegistry->methodCall(
						$this->expressionRegistry->variableName(
							new VariableNameIdentifier('#')
						),
						new MethodNameIdentifier('construct'),
						$this->expressionRegistry->constant(
							$this->valueRegistry->type($subtype)
						),
					)*/
				])
			)
		);
		return $subtype;
	}

	public function addSealed(
		TypeNameIdentifier $name,
		RecordType $valueType,
		Expression $constructorBody,
		Type|null $errorType
	): SealedType {
		$sealedType = $this->typeRegistryBuilder->addSealed($name, $valueType);

		$this->addConstructorMethod($name, $valueType, $errorType, $constructorBody);

		if (0) $this->customMethodRegistryBuilder->addMethod(
			$this->typeRegistry->atom(new TypeNameIdentifier('Constructor')),
			new MethodNameIdentifier($name->identifier),
			$valueType,
			$this->typeRegistry->nothing(),
			$errorType && !($errorType instanceof NothingType) ?
				$this->typeRegistry->result($valueType /*$subtype*/, $errorType) : $valueType/*$subtype*/,
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->sequence([
					$constructorBody,
					$this->expressionRegistry->variableName(
						new VariableNameIdentifier('#')
					),
				])
			)
		);

		if (0) $this->customMethodRegistryBuilder->addMethod(
			$this->typeRegistry->atom(new TypeNameIdentifier('Constructor')),
			new MethodNameIdentifier($name->identifier),
			$valueType,
			$this->typeRegistry->nothing(),
			$valueType, //$sealedType,
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->variableName(
					new VariableNameIdentifier('#')
				),
				/*$this->expressionRegistry->methodCall(
					$this->expressionRegistry->variableName(
						new VariableNameIdentifier('#')
					),
					new MethodNameIdentifier('construct'),
					$this->expressionRegistry->constant(
						$this->valueRegistry->type($sealedType)
					),
				)*/
			)
		);
		return $sealedType;
	}

	public function addConstructorMethod(
		TypeNameIdentifier $name,
		Type $fromType,
		Type|null $errorType,
		Expression $constructorBody
	): void {
		$this->customMethodRegistryBuilder->addMethod(
			$this->typeRegistry->atom(new TypeNameIdentifier('Constructor')),
			new MethodNameIdentifier('as' . $name->identifier),
			$fromType,
			$this->typeRegistry->nothing(),
			$errorType && !($errorType instanceof NothingType) ?
				$this->typeRegistry->result($fromType, $errorType) :
				$fromType,
			$this->expressionRegistry->functionBody(
				$this->expressionRegistry->sequence([
					$constructorBody,
					$this->expressionRegistry->variableName(new VariableNameIdentifier('#'))
				])
			)
		);
	}
}