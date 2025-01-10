<?php

namespace Walnut\Lang\Implementation\Program\Registry;

use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpressionDefault as MatchExpressionDefaultInterface;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpressionOperation;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpressionPair as MatchExpressionPairInterface;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\ExpressionRegistry as ExpressionRegistryInterface;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\Expression\ConstantExpression;
use Walnut\Lang\Implementation\Code\Expression\MatchExpression;
use Walnut\Lang\Implementation\Code\Expression\MatchExpressionDefault;
use Walnut\Lang\Implementation\Code\Expression\MatchExpressionEquals;
use Walnut\Lang\Implementation\Code\Expression\MatchExpressionIsSubtypeOf;
use Walnut\Lang\Implementation\Code\Expression\MatchExpressionPair;
use Walnut\Lang\Implementation\Code\Expression\MethodCallExpression;
use Walnut\Lang\Implementation\Code\Expression\MutableExpression;
use Walnut\Lang\Implementation\Code\Expression\NoErrorExpression;
use Walnut\Lang\Implementation\Code\Expression\NoExternalErrorExpression;
use Walnut\Lang\Implementation\Code\Expression\RecordExpression;
use Walnut\Lang\Implementation\Code\Expression\ReturnExpression;
use Walnut\Lang\Implementation\Code\Expression\SequenceExpression;
use Walnut\Lang\Implementation\Code\Expression\TupleExpression;
use Walnut\Lang\Implementation\Code\Expression\VariableAssignmentExpression;
use Walnut\Lang\Implementation\Code\Expression\VariableNameExpression;
use Walnut\Lang\Implementation\Function\FunctionBody;

final readonly class ExpressionRegistry implements ExpressionRegistryInterface {
	public function __construct(
		public TypeRegistry $typeRegistry,
		public ValueRegistry $valueRegistry,
	) {}

	/** @param list<MatchExpressionPairInterface|MatchExpressionDefaultInterface> $pairs */
	public function matchTrue(array $pairs): \Walnut\Lang\Blueprint\Code\Expression\MatchExpression {
		return $this->match(
			$this->constant($this->valueRegistry->true),
			new MatchExpressionEquals, array_map(
				fn(MatchExpressionPairInterface|MatchExpressionDefaultInterface $pair): MatchExpressionPairInterface|MatchExpressionDefaultInterface => match(true) {
					$pair instanceof MatchExpressionPairInterface => new MatchExpressionPair(
						$this->methodCall(
							$pair->matchExpression,
							new MethodNameIdentifier('asBoolean'),
							$this->constant(
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
		return $this->match($condition, new MatchExpressionIsSubtypeOf, $pairs);
	}

	/** @param list<MatchExpressionPairInterface|MatchExpressionDefaultInterface> $pairs */
	public function matchValue(Expression $condition, array $pairs): MatchExpression {
		return $this->match($condition, new MatchExpressionEquals, $pairs);
	}

	public function matchIf(Expression $condition, Expression $then, Expression $else): MatchExpression {
		return $this->match(
			$this->methodCall(
				$condition,
				new MethodNameIdentifier('asBoolean'),
				$this->constant(
					$this->valueRegistry->null
				)
			),
			new MatchExpressionEquals, [
			new MatchExpressionPair(
				$this->constant($this->valueRegistry->true),
				$then
			),
			new MatchExpressionDefault($else)
		]);
	}

	public function functionCall(Expression $target, Expression $parameter): \Walnut\Lang\Blueprint\Code\Expression\MethodCallExpression {
		return $this->methodCall(
			$target,
			new MethodNameIdentifier('invoke'),
			$parameter
		);
	}

	public function constructorCall(TypeNameIdentifier $typeName, Expression $parameter): MethodCallExpression {
		return $this->methodCall(
			$parameter,
			new MethodNameIdentifier('construct'),
			$this->constant(
				$this->valueRegistry->type(
					$this->typeRegistry->typeByName($typeName)
				)
			)
		);
	}

	public function propertyAccess(Expression $target, int|string $propertyName): MethodCallExpression {
		return $this->methodCall(
			$target,
			new MethodNameIdentifier('item'),
			$this->constant(
				is_int($propertyName) ?
					$this->valueRegistry->integer($propertyName) :
					$this->valueRegistry->string($propertyName)
			)
		);
	}
	
	public function constant(Value $value): ConstantExpression {
		return new ConstantExpression($value);
	}

	/** @param list<Expression> $values */
	public function tuple(array $values): TupleExpression {
		return new TupleExpression($values);
	}
	/** @param array<string, Expression> $values */
	public function record(array $values): RecordExpression {
		return new RecordExpression($values);
	}

	/** @param list<Expression> $values */
	public function sequence(array $values): SequenceExpression {
		return new SequenceExpression($values);
	}

	public function return(Expression $returnedExpression): ReturnExpression {
		return new ReturnExpression($returnedExpression);
	}
	public function noError(Expression $targetExpression): NoErrorExpression {
		return new NoErrorExpression($targetExpression);
	}
	public function noExternalError(Expression $targetExpression): NoExternalErrorExpression {
		return new NoExternalErrorExpression($targetExpression);
	}
	public function variableName(VariableNameIdentifier $variableName): VariableNameExpression {
		return new VariableNameExpression($variableName);
	}
	public function variableAssignment(
		VariableNameIdentifier $variableName,
		Expression $assignedExpression
	): VariableAssignmentExpression {
		return new VariableAssignmentExpression(
			$variableName,
			$assignedExpression
		);
	}

	/** @param list<MatchExpressionPairInterface|MatchExpressionDefaultInterface> $pairs */
	private function match(
		Expression $target,
		MatchExpressionOperation $operation,
		array $pairs
	): MatchExpression {
		return new MatchExpression(
			$target,
			$operation,
			$pairs
		);
	}

	public function matchPair(Expression $matchExpression, Expression $valueExpression): MatchExpressionPair {
		return new MatchExpressionPair($matchExpression, $valueExpression);
	}

	public function matchDefault(Expression $valueExpression): MatchExpressionDefault {
		return new MatchExpressionDefault($valueExpression);
	}

	public function methodCall(
		Expression $target,
		MethodNameIdentifier $methodName,
		Expression $parameter
	): MethodCallExpression {
		return new MethodCallExpression(
			$target,
			$methodName,
			$parameter
		);
	}

	public function functionBody(Expression $expression): FunctionBody {
		return new FunctionBody($expression);
	}

	public function mutable(Type $type, Expression $value): MutableExpression {
		return new MutableExpression(
			$type,
			$value
		);
	}
}