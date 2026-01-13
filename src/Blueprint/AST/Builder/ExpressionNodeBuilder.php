<?php

namespace Walnut\Lang\Blueprint\AST\Builder;

use Walnut\Lang\Blueprint\AST\Node\Expression\BooleanAndExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\BooleanNotExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\BooleanOrExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\BooleanXorExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\ConstantExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\ConstructorCallExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\DataExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\GroupExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\ScopedExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\FunctionCallExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchErrorExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchExpressionDefaultNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchExpressionPairNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchIfExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchTrueExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchTypeExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchValueExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MethodCallExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MultiVariableAssignmentExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MutableExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\NoErrorExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\NoExternalErrorExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\PropertyAccessExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\RecordExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\ReturnExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\SequenceExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\SetExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\TupleExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\VariableAssignmentExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\VariableNameExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\FunctionBodyNode;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\AST\Node\Value\ValueNode;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;

interface ExpressionNodeBuilder {
	public function constant(ValueNode $value): ConstantExpressionNode;
	public function data(TypeNameIdentifier $typeName, ExpressionNode $value): DataExpressionNode;
	public function constructorCall(TypeNameIdentifier $typeName, ExpressionNode $parameter): ConstructorCallExpressionNode;
	public function functionCall(ExpressionNode $target, ExpressionNode $parameter): FunctionCallExpressionNode;

	/** @param list<MatchExpressionPairNode|MatchExpressionDefaultNode> $pairs */
	public function matchTrue(array $pairs): MatchTrueExpressionNode;
	/** @param list<MatchExpressionPairNode|MatchExpressionDefaultNode> $pairs */
	public function matchType(ExpressionNode $target, array $pairs): MatchTypeExpressionNode;
	/** @param list<MatchExpressionPairNode|MatchExpressionDefaultNode> $pairs */
	public function matchValue(ExpressionNode $target, array $pairs): MatchValueExpressionNode;
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
		VariableNameIdentifier $variableName,
		ExpressionNode $assignedExpression
	): VariableAssignmentExpressionNode;

	public function variableName(VariableNameIdentifier $variableName): VariableNameExpressionNode;

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
		MethodNameIdentifier $methodName,
		ExpressionNode $parameter
	): MethodCallExpressionNode;

	public function booleanOr(ExpressionNode $first, ExpressionNode $second): BooleanOrExpressionNode;
	public function booleanAnd(ExpressionNode $first, ExpressionNode $second): BooleanAndExpressionNode;
	public function booleanXor(ExpressionNode $first, ExpressionNode $second): BooleanXorExpressionNode;
	public function booleanNot(ExpressionNode $expression): BooleanNotExpressionNode;

	/** @param array<VariableNameIdentifier> $variableNames */
	public function multiVariableAssignment(array $variableNames, ExpressionNode $assignedExpression): MultiVariableAssignmentExpressionNode;
}