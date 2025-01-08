<?php

namespace Walnut\Lang\Implementation\Compilation;

use Walnut\Lang\Blueprint\AST\Compiler\AstFunctionBodyCompiler;
use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\Code\Expression\ConstantExpression;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpression;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpressionDefault as MatchExpressionDefaultInterface;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpressionOperation;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpressionPair as MatchExpressionPairInterface;
use Walnut\Lang\Blueprint\Code\Expression\MethodCallExpression;
use Walnut\Lang\Blueprint\Code\Expression\MutableExpression;
use Walnut\Lang\Blueprint\Code\Expression\NoErrorExpression;
use Walnut\Lang\Blueprint\Code\Expression\NoExternalErrorExpression;
use Walnut\Lang\Blueprint\Code\Expression\RecordExpression;
use Walnut\Lang\Blueprint\Code\Expression\ReturnExpression;
use Walnut\Lang\Blueprint\Code\Expression\SequenceExpression;
use Walnut\Lang\Blueprint\Code\Expression\TupleExpression;
use Walnut\Lang\Blueprint\Code\Expression\VariableAssignmentExpression;
use Walnut\Lang\Blueprint\Code\Expression\VariableNameExpression;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Compilation\CodeBuilder as CodeBuilderInterface;
use Walnut\Lang\Blueprint\Compilation\CompilationException;
use Walnut\Lang\Blueprint\Function\CustomMethodDraft;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Blueprint\Function\FunctionBodyDraft;
use Walnut\Lang\Blueprint\Program\Builder\CustomMethodRegistryBuilder;
use Walnut\Lang\Blueprint\Program\Builder\ProgramBuilder;
use Walnut\Lang\Blueprint\Program\Builder\ProgramTypeBuilder;
use Walnut\Lang\Blueprint\Program\Registry\ExpressionRegistry;
use Walnut\Lang\Blueprint\Program\Registry\MethodRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Blueprint\Type\AliasType;
use Walnut\Lang\Blueprint\Type\AtomType;
use Walnut\Lang\Blueprint\Type\EnumerationType;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\SubtypeType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\Expression\MatchExpressionDefault;
use Walnut\Lang\Implementation\Code\Expression\MatchExpressionEquals;
use Walnut\Lang\Implementation\Code\Expression\MatchExpressionIsSubtypeOf;
use Walnut\Lang\Implementation\Code\Expression\MatchExpressionPair;

final readonly class CodeBuilder implements CodeBuilderInterface {

	public function __construct(
		public TypeRegistry $typeRegistry,
		public ValueRegistry $valueRegistry,
		private ProgramTypeBuilder $programTypeBuilder,
		private CustomMethodRegistryBuilder $customMethodRegistryBuilder,
		private ExpressionRegistry $expressionRegistry
	) {}

	/** @param list<MatchExpressionPairInterface|MatchExpressionDefaultInterface> $pairs */
	public function matchTrue(array $pairs): MatchExpression {
		return $this->expressionRegistry->match(
			$this->expressionRegistry->constant($this->valueRegistry->true),
			new MatchExpressionEquals, array_map(
				fn(MatchExpressionPairInterface|MatchExpressionDefaultInterface $pair): MatchExpressionPairInterface|MatchExpressionDefaultInterface => match(true) {
					$pair instanceof MatchExpressionPairInterface => new MatchExpressionPair(
						$this->expressionRegistry->methodCall(
							$pair->matchExpression,
							new MethodNameIdentifier('asBoolean'),
							$this->expressionRegistry->constant(
								$this->valueRegistry->null
							)
						),
						$pair->valueExpression
					),
					$pair instanceof MatchExpressionDefaultInterface => $pair
				},
				$pairs
			)
		);
	}

	/** @param list<MatchExpressionPairInterface|MatchExpressionDefaultInterface> $pairs */
	public function matchType(Expression $condition, array $pairs): MatchExpression {
		return $this->expressionRegistry->match($condition, new MatchExpressionIsSubtypeOf, $pairs);
	}

	/** @param list<MatchExpressionPairInterface|MatchExpressionDefaultInterface> $pairs */
	public function matchValue(Expression $condition, array $pairs): MatchExpression {
		return $this->expressionRegistry->match($condition, new MatchExpressionEquals, $pairs);
	}

	public function matchIf(Expression $condition, Expression $then, Expression $else): MatchExpression {
		return $this->expressionRegistry->match(
			$this->expressionRegistry->methodCall(
				$condition,
				new MethodNameIdentifier('asBoolean'),
				$this->expressionRegistry->constant(
					$this->valueRegistry->null
				)
			),
			new MatchExpressionEquals, [
			new MatchExpressionPair(
				$this->expressionRegistry->constant($this->valueRegistry->true),
				$then
			),
			new MatchExpressionDefault($else)
		]);
	}

	public function noError(Expression $targetExpression): NoErrorExpression {
		return $this->expressionRegistry->noError($targetExpression);
	}

	public function noExternalError(Expression $targetExpression): NoExternalErrorExpression {
		return $this->expressionRegistry->noExternalError($targetExpression);
	}

	public function functionCall(Expression $target, Expression $parameter): MethodCallExpression {
		return $this->expressionRegistry->methodCall(
			$target,
			new MethodNameIdentifier('invoke'),
			$parameter
		);
	}

	public function constructorCall(TypeNameIdentifier $typeName, Expression $parameter): MethodCallExpression {
		return $this->expressionRegistry->methodCall(
			$parameter,
			new MethodNameIdentifier('construct'),
			$this->expressionRegistry->constant(
				$this->valueRegistry->type(
					$this->typeRegistry->typeByName($typeName)
				)
			)
		);
	}

	public function propertyAccess(Expression $target, int|string $propertyName): MethodCallExpression {
		return $this->expressionRegistry->methodCall(
			$target,
			new MethodNameIdentifier('item'),
			$this->expressionRegistry->constant(
				is_int($propertyName) ?
					$this->valueRegistry->integer($propertyName) :
					$this->valueRegistry->string($propertyName)
			)
		);
	}

	/** @throws CompilationException */
	public function addConstructorMethod(
		TypeNameIdentifier $typeName,
		Type $parameterType,
		Type $dependencyType,
		Type $errorType,
		FunctionBodyDraft $functionBody
	): CustomMethodDraft {
		$type = $this->typeRegistry->typeByName($typeName);
		$returnType = match(true) {
			$type instanceof SealedType => $type->valueType,
			$type instanceof SubtypeType => $type->baseType,
			default => throw new CompilationException(
				"Constructors are only allowed for subtypes and sealed types",
			)
		};
		return $this->addMethodDraft(
			$this->typeRegistry->typeByName(new TypeNameIdentifier('Constructor')),
			new MethodNameIdentifier($typeName),
			$parameterType,
			$dependencyType,
			$errorType instanceof NothingType ? $returnType : $this->typeRegistry->result(
				$returnType, $errorType
			),
			$functionBody
		);
	}

	public function addMethodDraft(
		Type $targetType,
		MethodNameIdentifier $methodName,
		Type $parameterType,
		Type $dependencyType,
		Type $returnType,
		FunctionBodyDraft $functionBody
	): CustomMethodDraft {
		return $this->customMethodRegistryBuilder->addMethodDraft(
			$targetType,
			$methodName,
			$parameterType,
			$dependencyType,
			$returnType,
			$functionBody
		);
	}

	public function constant(Value $value): ConstantExpression {
		return $this->expressionRegistry->constant($value);
	}

	public function tuple(array $values): TupleExpression {
		return $this->expressionRegistry->tuple($values);
	}

	public function record(array $values): RecordExpression {
		return $this->expressionRegistry->record($values);
	}

	public function sequence(array $values): SequenceExpression {
		return $this->expressionRegistry->sequence($values);
	}

	public function return(Expression $returnedExpression): ReturnExpression {
		return $this->expressionRegistry->return($returnedExpression);
	}

	public function variableName(VariableNameIdentifier $variableName): VariableNameExpression {
		return $this->expressionRegistry->variableName($variableName);
	}

	public function variableAssignment(VariableNameIdentifier $variableName, Expression $assignedExpression): VariableAssignmentExpression {
		return $this->expressionRegistry->variableAssignment($variableName, $assignedExpression);
	}

	public function match(Expression $target, MatchExpressionOperation $operation, array $pairs): MatchExpression {
		return $this->expressionRegistry->match($target, $operation, $pairs);
	}

	public function matchPair(Expression $matchExpression, Expression $valueExpression): MatchExpressionPair {
		return $this->expressionRegistry->matchPair($matchExpression, $valueExpression);
	}

	public function matchDefault(Expression $valueExpression): MatchExpressionDefault {
		return $this->expressionRegistry->matchDefault($valueExpression);
	}

	public function methodCall(Expression $target, MethodNameIdentifier $methodName, Expression $parameter): MethodCallExpression {
		return $this->expressionRegistry->methodCall($target, $methodName, $parameter);
	}

	public function functionBodyDraft(ExpressionNode $expressionNode): FunctionBodyDraft {
		return $this->expressionRegistry->functionBodyDraft($expressionNode);
	}

	public function functionBody(Expression $expression): FunctionBody {
		return $this->expressionRegistry->functionBody($expression);
	}

	public function mutable(Type $type, Expression $value): MutableExpression {
		return $this->expressionRegistry->mutable($type, $value);
	}

	public function addAtom(TypeNameIdentifier $name): AtomType {
		return $this->programTypeBuilder->addAtom($name);
	}

	/** @param list<EnumValueIdentifier> $values */
	public function addEnumeration(TypeNameIdentifier $name, array $values): EnumerationType {
		return $this->programTypeBuilder->addEnumeration($name, $values);
	}

	public function addAlias(TypeNameIdentifier $name, Type $aliasedType): AliasType {
		return $this->programTypeBuilder->addAlias($name, $aliasedType);
	}

	public function addSubtype(TypeNameIdentifier $name, Type $baseType, ExpressionNode $constructorBody, ?Type $errorType): SubtypeType {
		return $this->programTypeBuilder->addSubtype($name, $baseType, $constructorBody, $errorType);
	}

	public function addSealed(
		TypeNameIdentifier $name,
		RecordType $valueType,
		ExpressionNode $constructorBody,
		Type|null $errorType
	): SealedType {
		return $this->programTypeBuilder->addSealed($name, $valueType, $constructorBody, $errorType);
	}

	public function build(AstFunctionBodyCompiler $astFunctionBodyCompiler): MethodRegistry {
		return $this->customMethodRegistryBuilder->build($astFunctionBodyCompiler);
	}
}