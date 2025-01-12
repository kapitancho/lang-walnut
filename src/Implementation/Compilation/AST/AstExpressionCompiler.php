<?php

namespace Walnut\Lang\Implementation\Compilation\AST;

use Walnut\Lang\Blueprint\AST\Node\Expression\ConstantExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\ConstructorCallExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\FunctionCallExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchExpressionDefaultNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchExpressionPairNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchIfExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchTrueExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchTypeExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchValueExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MethodCallExpressionNode;
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
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\AST\Node\Value\ValueNode;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpressionDefault;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpressionPair;
use Walnut\Lang\Blueprint\Compilation\AST\AstCompilationException;
use Walnut\Lang\Blueprint\Compilation\AST\AstExpressionCompiler as AstExpressionCompilerInterface;
use Walnut\Lang\Blueprint\Compilation\AST\AstTypeCompiler;
use Walnut\Lang\Blueprint\Compilation\AST\AstValueCompiler;
use Walnut\Lang\Blueprint\Program\Registry\ExpressionRegistry;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;

final readonly class AstExpressionCompiler implements AstExpressionCompilerInterface {
	public function __construct(
		private AstTypeCompiler    $astTypeCompiler,
		private AstValueCompiler   $astValueCompiler,
		private ExpressionRegistry $expressionRegistry,
	) {}

	/** @throws AstCompilationException */
	private function matchExpressionPair(MatchExpressionPairNode $matchExpressionPairNode): MatchExpressionPair {
		return $this->expressionRegistry->matchPair(
			$this->expression($matchExpressionPairNode->matchExpression),
			$this->expression($matchExpressionPairNode->valueExpression)
		);
	}

	/** @throws AstCompilationException */
	private function matchExpressionDefault(MatchExpressionDefaultNode $matchExpressionDefaultNode): MatchExpressionDefault {
		return $this->expressionRegistry->matchDefault(
			$this->expression($matchExpressionDefaultNode->valueExpression)
		);
	}

	/** @throws AstCompilationException */
	private function matchExpression(MatchExpressionPairNode|MatchExpressionDefaultNode $matchExpressionNode): MatchExpressionPair|MatchExpressionDefault {
		return match(true) {
			$matchExpressionNode instanceof MatchExpressionPairNode =>
				$this->matchExpressionPair($matchExpressionNode),
			$matchExpressionNode instanceof MatchExpressionDefaultNode =>
				$this->matchExpressionDefault($matchExpressionNode),
		};
	}

	/** @throws AstCompilationException */
	public function type(TypeNode $typeNode): Type {
		return $this->astTypeCompiler->type($typeNode);
	}

	/** @throws AstCompilationException */
	public function value(ValueNode $valueNode): Value {
		return $this->astValueCompiler->value($valueNode);
	}

	/** @throws AstCompilationException */
	public function expression(ExpressionNode $expressionNode): Expression {
		return match(true) {
			$expressionNode instanceof ConstantExpressionNode =>
				$this->expressionRegistry->constant(
					$this->value($expressionNode->value)
				),
			$expressionNode instanceof ConstructorCallExpressionNode =>
				$this->expressionRegistry->constructorCall(
					$expressionNode->typeName,
					$this->expression($expressionNode->parameter)
				),
			$expressionNode instanceof FunctionCallExpressionNode =>
				$this->expressionRegistry->functionCall(
					$this->expression($expressionNode->target),
					$this->expression($expressionNode->parameter)
				),
			/*$expressionNode instanceof MatchExpressionDefaultNode =>
				$this->codeBuilder->matchDefault(
					$this->expression($expressionNode->valueExpression)
				),
			$expressionNode instanceof MatchExpressionPairNode =>
				$this->codeBuilder->matchPair(
					$this->expression($expressionNode->matchExpression),
					$this->expression($expressionNode->valueExpression)
				),*/
			$expressionNode instanceof MatchIfExpressionNode =>
				$this->expressionRegistry->matchIf(
					$this->expression($expressionNode->condition),
					$this->expression($expressionNode->then),
					$this->expression($expressionNode->else)
				),
			$expressionNode instanceof MatchTrueExpressionNode =>
				$this->expressionRegistry->matchTrue(
					array_map($this->matchExpression(...), $expressionNode->pairs)
				),
			$expressionNode instanceof MatchTypeExpressionNode =>
				$this->expressionRegistry->matchType(
					$this->expression($expressionNode->target),
					array_map($this->matchExpression(...), $expressionNode->pairs)
				),
			$expressionNode instanceof MatchValueExpressionNode =>
				$this->expressionRegistry->matchValue(
					$this->expression($expressionNode->target),
					array_map($this->matchExpression(...), $expressionNode->pairs)
				),
			$expressionNode instanceof MethodCallExpressionNode =>
				$this->expressionRegistry->methodCall(
					$this->expression($expressionNode->target),
					$expressionNode->methodName,
					$this->expression($expressionNode->parameter)
				),
			$expressionNode instanceof MutableExpressionNode =>
				$this->expressionRegistry->mutable(
					$this->type($expressionNode->type),
					$this->expression($expressionNode->value),
				),
			$expressionNode instanceof NoErrorExpressionNode =>
				$this->expressionRegistry->noError(
					$this->expression($expressionNode->targetExpression),
				),
			$expressionNode instanceof NoExternalErrorExpressionNode =>
				$this->expressionRegistry->noExternalError(
					$this->expression($expressionNode->targetExpression),
				),
			$expressionNode instanceof PropertyAccessExpressionNode =>
				$this->expressionRegistry->propertyAccess(
					$this->expression($expressionNode->target),
					$expressionNode->propertyName
				),
			$expressionNode instanceof RecordExpressionNode =>
				$this->expressionRegistry->record(
					array_map($this->expression(...), $expressionNode->values)
				),
			$expressionNode instanceof ReturnExpressionNode =>
				$this->expressionRegistry->return(
					$this->expression($expressionNode->returnedExpression)
				),
			$expressionNode instanceof SetExpressionNode =>
				$this->expressionRegistry->set(
					array_map($this->expression(...), $expressionNode->values)
				),
			$expressionNode instanceof SequenceExpressionNode =>
				$this->expressionRegistry->sequence(
					array_map($this->expression(...), $expressionNode->expressions)
				),
			$expressionNode instanceof TupleExpressionNode =>
				$this->expressionRegistry->tuple(
					array_map($this->expression(...), $expressionNode->values)
				),
			$expressionNode instanceof VariableAssignmentExpressionNode =>
				$this->expressionRegistry->variableAssignment(
					$expressionNode->variableName,
					$this->expression($expressionNode->assignedExpression)
				),
			$expressionNode instanceof VariableNameExpressionNode =>
				$this->expressionRegistry->variableName(
					$expressionNode->variableName
				),
			true => throw new AstCompilationException(
				$expressionNode,
				"Unknown expression node type: " . get_class($expressionNode)
			)
		};
	}
}