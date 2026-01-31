<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Builder;

use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\BooleanAndExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\BooleanNotExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\BooleanOrExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\BooleanXorExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ConstantExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ConstructorCallExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\DataExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\FunctionCallExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\GroupExpressionNode;
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
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\ScopedExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\SequenceExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\SetExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\TupleExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\VariableAssignmentExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Expression\VariableNameExpressionNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\VariableNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\ValueNode;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\ExpressionRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\MatchExpressionDefault;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\MatchExpressionPair;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\UnknownType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\VariableName;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder\ExpressionBuilder as ExpressionCompilerInterface;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder\NameBuilder;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder\TypeBuilder;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder\ValueBuilder;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\BuildException;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\CodeMapper;


final readonly class ExpressionBuilder implements ExpressionCompilerInterface {
	public function __construct(
		private NameBuilder        $nameBuilder,
		private TypeBuilder        $typeBuilder,
		private ValueBuilder       $valueBuilder,
		private ValueRegistry      $valueRegistry,
		private ExpressionRegistry $expressionRegistry,
		private CodeMapper         $astCodeMapper,
	) {}

	/** @throws BuildException */
	private function matchExpressionPair(MatchExpressionPairNode $matchExpressionPairNode): MatchExpressionPair {
		return $this->expressionRegistry->matchPair(
			$this->expression($matchExpressionPairNode->matchExpression),
			$this->expression($matchExpressionPairNode->valueExpression)
		);
	}

	/** @throws BuildException */
	private function matchExpressionDefault(MatchExpressionDefaultNode $matchExpressionDefaultNode): MatchExpressionDefault {
		return $this->expressionRegistry->matchDefault(
			$this->expression($matchExpressionDefaultNode->valueExpression)
		);
	}

	/** @throws BuildException */
	public function type(TypeNode $typeNode): Type {
		return $this->typeBuilder->type($typeNode);
	}

	/** @throws BuildException */
	public function value(ValueNode $valueNode): Value {
		return $this->valueBuilder->value($valueNode);
	}

	/** @throws BuildException */
	private function constructorCall(ConstructorCallExpressionNode $expressionNode): Expression {
		try {
			return $this->expressionRegistry->constructorCall(
				$this->nameBuilder->typeName($expressionNode->typeName),
				$this->expression($expressionNode->parameter)
			);
		} catch (UnknownType $e) {
			throw new BuildException(
				$expressionNode,
				$e->getMessage(),
				$e
			);
		}
	}

	private function booleanNot(Expression $expression): Expression {
		$null = $this->expressionRegistry->constant(
			$this->valueRegistry->null
		);
		return $this->expressionRegistry->methodCall(
			$this->expressionRegistry->methodCall(
				$expression,
				$this->nameBuilder->methodName('asBoolean'),
				$null
			),
			$this->nameBuilder->methodName('unaryBitwiseNot'),
			$null
		);
	}

	private function booleanXor(Expression $first, Expression $second): Expression {
		$null = $this->expressionRegistry->constant(
			$this->valueRegistry->null
		);
		return $this->expressionRegistry->methodCall(
			$this->expressionRegistry->methodCall(
				$first,
				$this->nameBuilder->methodName('asBoolean'),
				$null
			),
			$this->nameBuilder->methodName('binaryNotEqual'),
			$this->expressionRegistry->methodCall(
				$second,
				$this->nameBuilder->methodName('asBoolean'),
				$null
			)
		);
	}


	/** @throws BuildException */
	public function expression(ExpressionNode $expressionNode): Expression {
		$result = match(true) {
			$expressionNode instanceof ConstantExpressionNode =>
				$this->expressionRegistry->constant(
					$this->value($expressionNode->value)
				),
			$expressionNode instanceof DataExpressionNode =>
				$this->expressionRegistry->data(
					$this->nameBuilder->typeName($expressionNode->typeName),
					$this->expression($expressionNode->value)
				),
			$expressionNode instanceof ConstructorCallExpressionNode =>
				$this->constructorCall($expressionNode),
			$expressionNode instanceof FunctionCallExpressionNode =>
				$this->expressionRegistry->functionCall(
					$this->expression($expressionNode->target),
					$this->expression($expressionNode->parameter)
				),
			$expressionNode instanceof MatchIfExpressionNode =>
				$this->expressionRegistry->matchIf(
					$this->expression($expressionNode->condition),
					$this->expression($expressionNode->then),
					$this->expression($expressionNode->else)
				),
			$expressionNode instanceof MatchErrorExpressionNode =>
				$this->expressionRegistry->matchError(
					$this->expression($expressionNode->condition),
					$this->expression($expressionNode->onError),
					$expressionNode->else ?
						$this->expression($expressionNode->else) : null
				),
			$expressionNode instanceof MatchTrueExpressionNode =>
				$this->expressionRegistry->matchTrue(
					array_map($this->matchExpressionPair(...), $expressionNode->pairs),
					$expressionNode->default ?
						$this->matchExpressionDefault($expressionNode->default) : null
				),
			$expressionNode instanceof MatchTypeExpressionNode =>
				$this->expressionRegistry->matchType(
					$this->expression($expressionNode->target),
					array_map($this->matchExpressionPair(...), $expressionNode->pairs),
					$expressionNode->default ?
						$this->matchExpressionDefault($expressionNode->default) : null
				),
			$expressionNode instanceof MatchValueExpressionNode =>
				$this->expressionRegistry->matchValue(
					$this->expression($expressionNode->target),
					array_map($this->matchExpressionPair(...), $expressionNode->pairs),
					$expressionNode->default ?
						$this->matchExpressionDefault($expressionNode->default) : null
				),
			$expressionNode instanceof MethodCallExpressionNode =>
				$this->expressionRegistry->methodCall(
					$this->expression($expressionNode->target),
					$this->nameBuilder->methodName($expressionNode->methodName),
					$this->expression($expressionNode->parameter)
				),
			$expressionNode instanceof ScopedExpressionNode =>
				$this->expressionRegistry->scoped(
					$this->expression($expressionNode->targetExpression),
				),
			$expressionNode instanceof BooleanOrExpressionNode =>
				$this->expressionRegistry->booleanOr(
					$this->expression($expressionNode->first),
					$this->expression($expressionNode->second)
				),
			$expressionNode instanceof BooleanAndExpressionNode =>
				$this->expressionRegistry->booleanAnd(
					$this->expression($expressionNode->first),
					$this->expression($expressionNode->second)
				),
			$expressionNode instanceof BooleanXorExpressionNode =>
				$this->booleanXor(
					$this->expression($expressionNode->first),
					$this->expression($expressionNode->second)
				),
			$expressionNode instanceof BooleanNotExpressionNode =>
				$this->booleanNot(
					$this->expression($expressionNode->expression)
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
			$expressionNode instanceof GroupExpressionNode =>
				$this->expressionRegistry->group(
					$this->expression($expressionNode->innerExpression)
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
					$this->nameBuilder->variableName($expressionNode->variableName),
					$this->expression($expressionNode->assignedExpression)
				),
			$expressionNode instanceof MultiVariableAssignmentExpressionNode =>
				$this->expressionRegistry->multiVariableAssignment(
					array_map(
						fn(VariableNameNode $variableName): VariableName =>
							$this->nameBuilder->variableName($variableName),
						$expressionNode->variableNames
					),
					$this->expression($expressionNode->assignedExpression)
				),
			$expressionNode instanceof VariableNameExpressionNode =>
				$this->expressionRegistry->variableName(
					$this->nameBuilder->variableName($expressionNode->variableName)
				),
			// @codeCoverageIgnoreStart
			true => throw new BuildException(
				$expressionNode,
				sprintf("Unknown expression node type: %s", get_class($expressionNode))
			)
			// @codeCoverageIgnoreEnd
		};
		$this->astCodeMapper->mapNode($expressionNode, $result);
		return $result;
	}
}