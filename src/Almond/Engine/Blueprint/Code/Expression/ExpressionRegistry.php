<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Expression;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\FunctionBody;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\VariableName;

interface ExpressionRegistry {
	public function constant(Value $value): ConstantExpression;
	public function variableName(VariableName $variableName): Expression;
	public function data(TypeName $typeName, Expression $value): Expression;
	public function group(Expression $expression): Expression;
	public function return(Expression $expression): Expression;
	public function scoped(Expression $expression): Expression;

	public function noError(Expression $expression): Expression;
	public function noExternalError(Expression $expression): Expression;

	public function booleanOr(Expression $first, Expression $second): Expression;
	public function booleanAnd(Expression $first, Expression $second): Expression;

	public function matchPair(Expression $matchExpression, Expression $valueExpression): MatchExpressionPair;
	public function matchDefault(Expression $valueExpression): MatchExpressionDefault;

	/** @param list<MatchExpressionPair> $pairs */
	public function matchTrue(array $pairs, MatchExpressionDefault|null $default): Expression;
	/** @param list<MatchExpressionPair> $pairs */
	public function matchType(Expression $condition, array $pairs, MatchExpressionDefault|null $default): Expression;
	/** @param list<MatchExpressionPair> $pairs */
	public function matchValue(Expression $condition, array $pairs, MatchExpressionDefault|null $default): Expression;
	public function matchIf(Expression $condition, Expression $then, Expression|null $else): Expression;

	public function matchError(
		Expression $condition, Expression $onError, Expression|null $else
	): Expression;

	/** @param list<Expression> $expressions */
	public function sequence(array $expressions): Expression;
	/** @param list<Expression> $expressions */
	public function tuple(array $expressions): Expression;

	public function methodCall(Expression $target, MethodName $methodName, Expression $parameter): Expression;

	public function functionCall(Expression $target, Expression $parameter): Expression;
	public function constructorCall(TypeName $typeName, Expression $parameter): Expression;
	public function propertyAccess(Expression $target, int|string $propertyName): Expression;

	public function variableAssignment(VariableName $variableName, Expression $assignedExpression): Expression;
	/** @param array<VariableName> $variableNames */
	public function multiVariableAssignment(
		array $variableNames,
		Expression $assignedExpression
	): Expression;

	public function functionBody(Expression $expression): FunctionBody;
}