<?php

namespace Walnut\Lang\Implementation\AST\Builder;

use Walnut\Lang\Blueprint\AST\Builder\ExpressionNodeBuilder as ExpressionNodeBuilderInterface;
use Walnut\Lang\Blueprint\AST\Builder\SourceLocator;
use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchExpressionDefaultNode as MatchExpressionDefaultNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Expression\MethodCallExpressionNode as MethodCallExpressionNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Expression\MultiVariableAssignmentExpressionNode as MultiVariableAssignmentExpressionNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation as SourceLocationInterface;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\AST\Node\Value\ValueNode;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Implementation\AST\Node\Expression\BooleanAndExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\BooleanNotExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\BooleanOrExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\BooleanXorExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\ConstantExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\ConstructorCallExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\DataExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\GroupExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\ScopedExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\FunctionCallExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\MatchErrorExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\MatchExpressionDefaultNode;
use Walnut\Lang\Implementation\AST\Node\Expression\MatchExpressionPairNode;
use Walnut\Lang\Implementation\AST\Node\Expression\MatchIfExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\MatchTrueExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\MatchTypeExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\MatchValueExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\MethodCallExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\MultiVariableAssignmentExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\MutableExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\NoErrorExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\NoExternalErrorExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\PropertyAccessExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\RecordExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\ReturnExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\SequenceExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\SetExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\TupleExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\VariableAssignmentExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\VariableNameExpressionNode;

final readonly class ExpressionNodeBuilder implements ExpressionNodeBuilderInterface {
	public function __construct(private readonly SourceLocator $sourceLocator) {}

	private function getSourceLocation(): SourceLocationInterface {
		return $this->sourceLocator->getSourceLocation();
	}

	public function constant(ValueNode $value): ConstantExpressionNode {
		return new ConstantExpressionNode($this->getSourceLocation(), $value);
	}

	public function data(TypeNameIdentifier $typeName, ExpressionNode $value): DataExpressionNode {
		return new DataExpressionNode($this->getSourceLocation(), $typeName, $value);
	}

	public function constructorCall(
		TypeNameIdentifier $typeName,
		ExpressionNode $parameter
	): ConstructorCallExpressionNode {
		return new ConstructorCallExpressionNode(
			$this->getSourceLocation(),
			$typeName,
			$parameter
		);
	}

	public function functionCall(ExpressionNode $target, ExpressionNode $parameter): FunctionCallExpressionNode {
		return new FunctionCallExpressionNode($this->getSourceLocation(), $target, $parameter);
	}

	/** @param MatchExpressionDefaultNodeInterface $pairs */
	public function matchTrue(array $pairs): MatchTrueExpressionNode {
		return new MatchTrueExpressionNode($this->getSourceLocation(), $pairs);
	}

	/** @param MatchExpressionDefaultNodeInterface $pairs */
	public function matchType(ExpressionNode $target, array $pairs): MatchTypeExpressionNode {
		return new MatchTypeExpressionNode($this->getSourceLocation(), $target, $pairs);
	}

	/** @param MatchExpressionDefaultNodeInterface $pairs */
	public function matchValue(ExpressionNode $target, array $pairs): MatchValueExpressionNode {
		return new MatchValueExpressionNode($this->getSourceLocation(), $target, $pairs);
	}

	public function matchIf(ExpressionNode $condition, ExpressionNode $then, ExpressionNode $else): MatchIfExpressionNode {
		return new MatchIfExpressionNode($this->getSourceLocation(), $condition, $then, $else);
	}

	public function matchError(ExpressionNode $condition, ExpressionNode $then, ExpressionNode|null $else): MatchErrorExpressionNode {
		return new MatchErrorExpressionNode($this->getSourceLocation(), $condition, $then, $else);
	}

	public function scoped(ExpressionNode $targetExpression): ScopedExpressionNode {
		return new ScopedExpressionNode($this->getSourceLocation(), $targetExpression);
	}

	public function mutable(TypeNode $type, ExpressionNode $value): MutableExpressionNode {
		return new MutableExpressionNode($this->getSourceLocation(), $type, $value);
	}

	public function noError(ExpressionNode $targetExpression): NoErrorExpressionNode {
		return new NoErrorExpressionNode($this->getSourceLocation(), $targetExpression);
	}

	public function noExternalError(ExpressionNode $targetExpression): NoExternalErrorExpressionNode {
		return new NoExternalErrorExpressionNode($this->getSourceLocation(), $targetExpression);
	}

	public function propertyAccess(ExpressionNode $target, int|string $propertyName): PropertyAccessExpressionNode {
		return new PropertyAccessExpressionNode($this->getSourceLocation(), $target, $propertyName);
	}

	public function return(ExpressionNode $returnedExpression): ReturnExpressionNode {
		return new ReturnExpressionNode($this->getSourceLocation(), $returnedExpression);
	}

	public function group(ExpressionNode $innerExpression): GroupExpressionNode {
		return new GroupExpressionNode($this->getSourceLocation(), $innerExpression);
	}

	/** @param list<ExpressionNode> $expressions */
	public function sequence(array $expressions): SequenceExpressionNode {
		return new SequenceExpressionNode($this->getSourceLocation(), $expressions);
	}

	public function variableAssignment(VariableNameIdentifier $variableName, ExpressionNode $assignedExpression): VariableAssignmentExpressionNode {
		return new VariableAssignmentExpressionNode($this->getSourceLocation(), $variableName, $assignedExpression);
	}

	public function variableName(VariableNameIdentifier $variableName): VariableNameExpressionNode {
		return new VariableNameExpressionNode($this->getSourceLocation(), $variableName);
	}

	/** @param list<ExpressionNode> $values */
	public function tuple(array $values): TupleExpressionNode {
		return new TupleExpressionNode($this->getSourceLocation(), $values);
	}

	/** @param array<string, ExpressionNode> $values */
	public function record(array $values): RecordExpressionNode {
		return new RecordExpressionNode($this->getSourceLocation(), $values);
	}

	/** @param list<ExpressionNode> $values */
	public function set(array $values): SetExpressionNode {
		return new SetExpressionNode($this->getSourceLocation(), $values);
	}

	public function matchPair(ExpressionNode $matchExpression, ExpressionNode $valueExpression): MatchExpressionPairNode {
		return new MatchExpressionPairNode($this->getSourceLocation(), $matchExpression, $valueExpression);
	}

	public function matchDefault(ExpressionNode $valueExpression): MatchExpressionDefaultNode {
		return new MatchExpressionDefaultNode($this->getSourceLocation(), $valueExpression);
	}

	public function methodCall(ExpressionNode $target, MethodNameIdentifier $methodName, ExpressionNode $parameter): MethodCallExpressionNodeInterface {
		return new MethodCallExpressionNode($this->getSourceLocation(), $target, $methodName, $parameter);
	}

	public function booleanOr(ExpressionNode $first, ExpressionNode $second): BooleanOrExpressionNode {
		return new BooleanOrExpressionNode($this->getSourceLocation(), $first, $second);
	}
	public function booleanAnd(ExpressionNode $first, ExpressionNode $second): BooleanAndExpressionNode {
		return new BooleanAndExpressionNode($this->getSourceLocation(), $first, $second);
	}
	public function booleanXor(ExpressionNode $first, ExpressionNode $second): BooleanXorExpressionNode {
		return new BooleanXorExpressionNode($this->getSourceLocation(), $first, $second);
	}
	public function booleanNot(ExpressionNode $expression): BooleanNotExpressionNode {
		return new BooleanNotExpressionNode($this->getSourceLocation(), $expression);
	}

	/** @param array<VariableNameIdentifier> $variableNames */
	public function multiVariableAssignment(array $variableNames, ExpressionNode $assignedExpression): MultiVariableAssignmentExpressionNodeInterface {
		return new MultiVariableAssignmentExpressionNode(
			$this->sourceLocator->getSourceLocation(),
			$variableNames,
			$assignedExpression
		);
	}

}