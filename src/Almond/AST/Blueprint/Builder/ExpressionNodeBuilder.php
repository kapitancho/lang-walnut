<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Builder;

use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\BooleanAndExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\BooleanNotExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\BooleanOrExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\BooleanXorExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ConstantExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ConstructorCallExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\DataExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\GroupExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ScopedExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\FunctionCallExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\MatchErrorExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\MatchExpressionDefaultNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\MatchExpressionPairNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\MatchIfExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\MatchTrueExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\MatchTypeExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\MatchValueExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\MethodCallExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\MultiVariableAssignmentExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\MutableExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\NoErrorExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\NoExternalErrorExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\PropertyAccessExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\RecordExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ReturnExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\SequenceExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\SetExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\TupleExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\VariableAssignmentExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\VariableNameExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\MethodNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\TypeNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\VariableNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\ValueNode;

interface ExpressionNodeBuilder {
	public function constant(ValueNode $value): ConstantExpressionNode;
	public function data(TypeNameNode $typeName, ExpressionNode $value): DataExpressionNode;
	public function constructorCall(TypeNameNode $typeName, ExpressionNode $parameter): ConstructorCallExpressionNode;
	public function functionCall(ExpressionNode $target, ExpressionNode $parameter): FunctionCallExpressionNode;

	/** @param list<MatchExpressionPairNode> $pairs */
	public function matchTrue(array $pairs, MatchExpressionDefaultNode|null $default): MatchTrueExpressionNode;
	/** @param list<MatchExpressionPairNode> $pairs */
	public function matchType(ExpressionNode $target, array $pairs, MatchExpressionDefaultNode|null $default): MatchTypeExpressionNode;
	/** @param list<MatchExpressionPairNode> $pairs */
	public function matchValue(ExpressionNode $target, array $pairs, MatchExpressionDefaultNode|null $default): MatchValueExpressionNode;
	public function matchIf(ExpressionNode $condition, ExpressionNode $then, ExpressionNode $else): MatchIfExpressionNode;
	public function matchError(ExpressionNode $condition, ExpressionNode $then, ExpressionNode|null $else): MatchErrorExpressionNode;

	public function scoped(ExpressionNode $targetExpression): ScopedExpressionNode;
	public function mutable(TypeNode $type, ExpressionNode $value): MutableExpressionNode;

	public function noError(ExpressionNode $targetExpression): NoErrorExpressionNode;
	public function noExternalError(ExpressionNode $targetExpression): NoExternalErrorExpressionNode;

	public function propertyAccess(ExpressionNode $target, int|string $propertyName): PropertyAccessExpressionNode;
	public function return(ExpressionNode $returnedExpression): ReturnExpressionNode;

	public function group(ExpressionNode $innerExpression): GroupExpressionNode;
	/** @param list<ExpressionNode> $expressions */
	public function sequence(array $expressions): SequenceExpressionNode;

	public function variableAssignment(
		VariableNameNode $variableName,
		ExpressionNode $assignedExpression
	): VariableAssignmentExpressionNode;

	public function variableName(VariableNameNode $variableName): VariableNameExpressionNode;

	/** @param list<ExpressionNode> $values */
	public function tuple(array $values): TupleExpressionNode;
	/** @param array<string, ExpressionNode> $values */
	public function record(array $values): RecordExpressionNode;
	/** @param list<ExpressionNode> $values */
	public function set(array $values): SetExpressionNode;

	public function matchPair(ExpressionNode $matchExpression, ExpressionNode $valueExpression): MatchExpressionPairNode;

	public function matchDefault(ExpressionNode $valueExpression): MatchExpressionDefaultNode;

	public function methodCall(
		ExpressionNode $target,
		MethodNameNode $methodName,
		ExpressionNode $parameter
	): MethodCallExpressionNode;

	public function booleanOr(ExpressionNode $first, ExpressionNode $second): BooleanOrExpressionNode;
	public function booleanAnd(ExpressionNode $first, ExpressionNode $second): BooleanAndExpressionNode;
	public function booleanXor(ExpressionNode $first, ExpressionNode $second): BooleanXorExpressionNode;
	public function booleanNot(ExpressionNode $expression): BooleanNotExpressionNode;

	/** @param array<VariableNameNode> $variableNames */
	public function multiVariableAssignment(array $variableNames, ExpressionNode $assignedExpression): MultiVariableAssignmentExpressionNode;
}