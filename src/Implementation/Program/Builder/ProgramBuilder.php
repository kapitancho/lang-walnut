<?php

namespace Walnut\Lang\Implementation\Program\Builder;

use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Function\CustomMethodDraft;
use Walnut\Lang\Blueprint\Function\FunctionBodyDraft;
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

	/** @throws AnalyserException */
	public function analyseAndBuildProgram(): Program {
		$analyseErrors = $this->customMethodRegistryBuilder->analyse();
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

	public function addMethodDraft(
		Type $targetType,
		MethodNameIdentifier $methodName,
		Type $parameterType,
		Type $dependencyType,
		Type $returnType,
		FunctionBodyDraft $functionBody,
	): CustomMethodDraft {
		return $this->customMethodRegistryBuilder->addMethodDraft(
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
		ExpressionNode $constructorBody,
		Type|null $errorType
	): SubtypeType {
		$subtype = $this->typeRegistryBuilder->addSubtype($name, $baseType);

		$this->addConstructorMethod($name, $baseType, $errorType, $constructorBody);

		return $subtype;
	}

	public function addSealed(
		TypeNameIdentifier $name,
		RecordType $valueType,
		ExpressionNode $constructorBody,
		Type|null $errorType
	): SealedType {
		$sealedType = $this->typeRegistryBuilder->addSealed($name, $valueType);

		$this->addConstructorMethod($name, $valueType, $errorType, $constructorBody);
		return $sealedType;
	}

	public function addConstructorMethod(
		TypeNameIdentifier $name,
		Type $fromType,
		Type|null $errorType,
		ExpressionNode $constructorBody
	): void {
		$this->customMethodRegistryBuilder->addMethodDraft(
			$this->typeRegistry->atom(new TypeNameIdentifier('Constructor')),
			new MethodNameIdentifier('as' . $name->identifier),
			$fromType,
			$this->typeRegistry->nothing,
			$errorType && !($errorType instanceof NothingType) ?
				$this->typeRegistry->result($fromType, $errorType) :
				$fromType,
			$this->expressionRegistry->functionBodyDraft(
				$constructorBody,
			)
		);
	}
}