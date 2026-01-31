<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Expression;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\ConstantExpression as ConstantExpressionInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\ExpressionRegistry as ExpressionRegistryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\MatchExpressionDefault as MatchExpressionDefaultInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\MatchExpressionPair as MatchExpressionPairInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\MatchExpressionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\FunctionBody as FunctionBodyInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\MethodContext;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Implementation\Code\Function\FunctionBody;

final readonly class ExpressionRegistry implements ExpressionRegistryInterface {
	public function __construct(
		private TypeRegistry      $typeRegistry,
		private ValueRegistry     $valueRegistry,
		private ValidationFactory $validationFactory,
		private MethodContext     $methodContext,
	) {}

	public function constant(Value $value): ConstantExpressionInterface {
		return new ConstantExpression(
			$this->validationFactory,
			$value
		);
	}

	public function variableName(VariableName $variableName): Expression {
		return new VariableNameExpression($variableName);
	}

	public function data(TypeName $typeName, Expression $value): Expression {
		return new DataExpression(
			$this->typeRegistry,
			$this->valueRegistry,
			$typeName,
			$value
		);
	}

	public function group(Expression $expression): Expression {
		return new GroupExpression($expression);
	}

	public function return(Expression $expression): Expression {
		return new ReturnExpression($this->typeRegistry, $expression);
	}
	public function scoped(Expression $expression): Expression {
		return new ScopedExpression($this->typeRegistry, $expression);
	}

	public function noError(Expression $expression): Expression {
		return new NoErrorExpression($this->typeRegistry, $expression);
	}
	public function noExternalError(Expression $expression): Expression {
		return new NoExternalErrorExpression($this->typeRegistry, $expression);
	}

	public function booleanOr(Expression $first, Expression $second): Expression {
		return new BooleanOrExpression(
			$this->typeRegistry,
			$this->valueRegistry, $this->methodContext,
			$first, $second
		);
	}
	public function booleanAnd(Expression $first, Expression $second): Expression {
		return new BooleanAndExpression(
			$this->typeRegistry,
			$this->valueRegistry, $this->methodContext,
			$first, $second
		);
	}


	public function matchPair(Expression $matchExpression, Expression $valueExpression): MatchExpressionPair {
		return new MatchExpressionPair($matchExpression, $valueExpression);
	}
	public function matchDefault(Expression $valueExpression): MatchExpressionDefault {
		return new MatchExpressionDefault($valueExpression);
	}

	/** @param list<MatchExpressionPairInterface> $pairs */
	public function matchTrue(array $pairs, MatchExpressionDefaultInterface|null $default): Expression {
		return new MatchExpression(
			$this->typeRegistry,
			$this->valueRegistry,
			MatchExpressionType::isTrue,
			$this->constant($this->valueRegistry->true),
			new MatchExpressionEquals,
			array_map(
				fn(MatchExpressionPairInterface $pair): MatchExpressionPairInterface => new MatchExpressionPair(
					$this->methodCall(
						$pair->matchExpression,
						new MethodName('asBoolean'),
						$this->constant($this->valueRegistry->null)
					),
					$pair->valueExpression
				),
				$pairs
			),
			$default
		);
	}

	/** @param list<MatchExpressionPairInterface> $pairs */
	public function matchType(Expression $condition,
		array $pairs, MatchExpressionDefaultInterface|null $default
	): Expression {
		return new MatchExpression(
			$this->typeRegistry,
			$this->valueRegistry,
			MatchExpressionType::typeOf,
			$condition,
			new MatchExpressionIsSubtypeOf(),
			$pairs,
			$default
		);
	}
	/** @param list<MatchExpressionPairInterface> $pairs */
	public function matchValue(Expression $condition,
		array $pairs, MatchExpressionDefaultInterface|null $default
	): Expression {
		return new MatchExpression(
			$this->typeRegistry,
			$this->valueRegistry,
			MatchExpressionType::valueOf,
			$condition,
			new MatchExpressionEquals(),
			$pairs,
			$default
		);
	}

	public function matchIf(Expression $condition, Expression $then, Expression|null $else): Expression {
		return new MatchExpression(
			$this->typeRegistry,
			$this->valueRegistry,
			MatchExpressionType::if,
			$this->methodCall(
				$condition,
				new MethodName('asBoolean'),
				$this->constant($this->valueRegistry->null)
			),
			new MatchExpressionEquals(),
			[
				new MatchExpressionPair(
					$this->constant($this->valueRegistry->true),
					$then
				)
			],
			new MatchExpressionDefault($else)
		);
	}

	public function matchError(
		Expression $condition, Expression $onError, Expression|null $else
	): MatchErrorExpression {
		return new MatchErrorExpression(
			$this->typeRegistry,
			$condition, $onError, $else
		);
	}


	/** @param list<Expression> $expressions */
	public function sequence(array $expressions): Expression {
		return new SequenceExpression(
			$this->typeRegistry, $this->valueRegistry, $expressions);
	}

	/** @param list<Expression> $expressions */
	public function tuple(array $expressions): Expression {
		return new TupleExpression(
			$this->typeRegistry,
			$this->valueRegistry,
			$expressions
		);
	}

	/** @param list<Expression> $expressions */
	public function set(array $expressions): Expression {
		return new SetExpression(
			$this->typeRegistry,
			$this->valueRegistry,
			$expressions
		);
	}

	/** @param array<string, Expression> $expressions */
	public function record(array $expressions): Expression {
		return new RecordExpression(
			$this->typeRegistry,
			$this->valueRegistry,
			$expressions
		);
	}

	public function mutable(Type $type, Expression $value): Expression {
		return new MutableExpression(
			$this->typeRegistry,
			$this->valueRegistry,
			$type,
			$value
		);
	}

	public function methodCall(Expression $target, MethodName $methodName, Expression $parameter): Expression {
		return new MethodCallExpression(
			$this->typeRegistry, $this->methodContext,
			$target, $methodName, $parameter
		);
	}

	public function functionCall(Expression $target, Expression $parameter): Expression {
		return $this->methodCall(
			$target,
			new MethodName('invoke'),
			$parameter
		);
	}

	public function constructorCall(TypeName $typeName, Expression $parameter): Expression {
		return $this->methodCall(
			$parameter,
			new MethodName('construct'),
			$this->constant(
				$this->valueRegistry->type(
					$this->typeRegistry->typeByName($typeName)
				)
			)
		);
	}

	public function propertyAccess(Expression $target, int|string $propertyName): Expression {
		return $this->methodCall(
			$target,
			new MethodName('item'),
			$this->constant(
				is_int($propertyName) ?
					$this->valueRegistry->integer($propertyName) :
					$this->valueRegistry->string($propertyName)
			)
		);
	}

	public function variableAssignment(VariableName $variableName, Expression $assignedExpression): Expression {
		return new VariableAssignmentExpression($variableName, $assignedExpression);
	}

	/** @param array<VariableName> $variableNames */
	public function multiVariableAssignment(
		array $variableNames,
		Expression $assignedExpression
	): Expression {
		return new MultiVariableAssignmentExpression(
			$this->typeRegistry,
			$this->valueRegistry,
			$this->methodContext,

			$variableNames,
			$assignedExpression
		);
	}

	public function functionBody(Expression $expression): FunctionBodyInterface {
		return new FunctionBody(
			$this->typeRegistry,
			$this->validationFactory,
			$expression
		);
	}
}