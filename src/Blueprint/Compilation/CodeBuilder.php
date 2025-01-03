<?php

namespace Walnut\Lang\Blueprint\Compilation;

use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpression;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpressionDefault;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpressionPair;
use Walnut\Lang\Blueprint\Code\Expression\MethodCallExpression;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Function\CustomMethod;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Blueprint\Program\Builder\CustomMethodRegistryBuilder;
use Walnut\Lang\Blueprint\Program\Builder\ProgramTypeBuilder;
use Walnut\Lang\Blueprint\Program\Registry\ExpressionRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;

interface CodeBuilder extends ExpressionRegistry, ProgramTypeBuilder, CustomMethodRegistryBuilder {
	public TypeRegistry $typeRegistry { get; }
	public ValueRegistry $valueRegistry { get; }

	/** @param list<MatchExpressionPair|MatchExpressionDefault> $pairs */
	public function matchTrue(array $pairs): MatchExpression;
	/** @param list<MatchExpressionPair|MatchExpressionDefault> $pairs */
	public function matchType(Expression $condition, array $pairs): MatchExpression;
	/** @param list<MatchExpressionPair|MatchExpressionDefault> $pairs */
	public function matchValue(Expression $condition, array $pairs): MatchExpression;
	public function matchIf(Expression $condition, Expression $then, Expression $else): MatchExpression;

	public function addVariable(VariableNameIdentifier $name, Value $value): void;
	public function functionCall(Expression $target, Expression $parameter): MethodCallExpression;
	public function constructorCall(
		TypeNameIdentifier $typeName,
		Expression $parameter
	): MethodCallExpression;
	public function propertyAccess(Expression $target, int|string $propertyName): MethodCallExpression;

	/** @throws CompilationException */
	public function addConstructorMethod(
		TypeNameIdentifier $typeName,
		Type $parameterType,
		Type $dependencyType,
		Type $errorType,
		FunctionBody $functionBody
	): CustomMethod;
}