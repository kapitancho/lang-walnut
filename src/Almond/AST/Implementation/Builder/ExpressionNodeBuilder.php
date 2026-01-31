<?php

namespace Walnut\Lang\Almond\AST\Implementation\Builder;

use Walnut\Lang\Almond\AST\Blueprint\Builder\ExpressionNodeBuilder as ExpressionNodeBuilderInterface;
use Walnut\Lang\Almond\AST\Blueprint\Builder\SourceLocator;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\MatchExpressionDefaultNode as MatchExpressionDefaultNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\MatchExpressionPairNode as MatchExpressionPairNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\MethodCallExpressionNode as MethodCallExpressionNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\MultiVariableAssignmentExpressionNode as MultiVariableAssignmentExpressionNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\MethodNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\TypeNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\VariableNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\ValueNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\BooleanAndExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\BooleanNotExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\BooleanOrExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\BooleanXorExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\ConstantExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\ConstructorCallExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\DataExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\FunctionCallExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\GroupExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\MatchErrorExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\MatchExpressionDefaultNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\MatchExpressionPairNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\MatchIfExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\MatchTrueExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\MatchTypeExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\MatchValueExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\MethodCallExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\MultiVariableAssignmentExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\MutableExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\NoErrorExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\NoExternalErrorExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\PropertyAccessExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\RecordExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\ReturnExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\ScopedExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\SequenceExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\SetExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\TupleExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\VariableAssignmentExpressionNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Expression\VariableNameExpressionNode;

final readonly class ExpressionNodeBuilder implements ExpressionNodeBuilderInterface {
	public function __construct(private SourceLocator $sourceLocator) {}

	private function getSourceLocation(): SourceLocation {
		return $this->sourceLocator->getSourceLocation();
	}

	public function constant(ValueNode $value): ConstantExpressionNode {
		return new ConstantExpressionNode($this->getSourceLocation(), $value);
	}

	public function data(TypeNameNode $typeName, ExpressionNode $value): DataExpressionNode {
		return new DataExpressionNode($this->getSourceLocation(), $typeName, $value);
	}

	public function constructorCall(
		TypeNameNode $typeName,
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

	/** @param list<MatchExpressionPairNodeInterface> $pairs */
	public function matchTrue(array $pairs, MatchExpressionDefaultNodeInterface|null $default): MatchTrueExpressionNode {
		return new MatchTrueExpressionNode($this->getSourceLocation(), $pairs, $default);
	}

	/** @param list<MatchExpressionPairNodeInterface> $pairs */
	public function matchType(ExpressionNode $target, array $pairs, MatchExpressionDefaultNodeInterface|null $default): MatchTypeExpressionNode {
		return new MatchTypeExpressionNode($this->getSourceLocation(), $target, $pairs, $default);
	}

	/** @param list<MatchExpressionPairNodeInterface> $pairs */
	public function matchValue(ExpressionNode $target, array $pairs, MatchExpressionDefaultNodeInterface|null $default): MatchValueExpressionNode {
		return new MatchValueExpressionNode($this->getSourceLocation(), $target, $pairs, $default);
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

	public function variableAssignment(VariableNameNode $variableName, ExpressionNode $assignedExpression): VariableAssignmentExpressionNode {
		return new VariableAssignmentExpressionNode($this->getSourceLocation(), $variableName, $assignedExpression);
	}

	public function variableName(VariableNameNode $variableName): VariableNameExpressionNode {
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

	public function methodCall(ExpressionNode $target, MethodNameNode $methodName, ExpressionNode $parameter): MethodCallExpressionNodeInterface {
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

	/** @param array<VariableNameNode> $variableNames */
	public function multiVariableAssignment(array $variableNames, ExpressionNode $assignedExpression): MultiVariableAssignmentExpressionNodeInterface {
		return new MultiVariableAssignmentExpressionNode(
			$this->sourceLocator->getSourceLocation(),
			$variableNames,
			$assignedExpression
		);
	}

}