<?php

namespace Walnut\Lang\Blueprint\Program\Registry;

use Walnut\Lang\Blueprint\Code\Expression\ConstantExpression;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpression;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpressionDefault;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpressionOperation;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpressionPair;
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
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;

interface ExpressionRegistry {
	public function constant(Value $value): ConstantExpression;

	/** @param list<Expression> $values */
	public function tuple(array $values): TupleExpression;
	/** @param array<string, Expression> $values */
	public function record(array $values): RecordExpression;

	/** @param list<Expression> $values */
	public function sequence(array $values): SequenceExpression;

	public function return(Expression $returnedExpression): ReturnExpression;
	public function noError(Expression $targetExpression): NoErrorExpression;
	public function noExternalError(Expression $targetExpression): NoExternalErrorExpression;
	public function variableName(VariableNameIdentifier $variableName): VariableNameExpression;
	public function variableAssignment(
		VariableNameIdentifier $variableName,
		Expression $assignedExpression
	): VariableAssignmentExpression;

	/** @param list<MatchExpressionPair|MatchExpressionDefault> $pairs */
	public function match(
		Expression $target,
		MatchExpressionOperation $operation,
		array $pairs
	): MatchExpression;

	public function matchPair(Expression $matchExpression, Expression $valueExpression): MatchExpressionPair;

	public function matchDefault(Expression $valueExpression): MatchExpressionDefault;

	public function methodCall(
		Expression $target,
		MethodNameIdentifier $methodName,
		Expression $parameter
	): MethodCallExpression;

	public function functionBody(Expression $expression): FunctionBody;
	public function mutable(Type $type, Expression $value): MutableExpression;
}