<?php

namespace Walnut\Lang\Implementation\Program\Registry;

use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpressionDefault as MatchExpressionDefaultInterface;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpressionPair as MatchExpressionPairInterface;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpressionOperation;
use Walnut\Lang\Blueprint\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\ExpressionRegistry as ExpressionRegistryInterface;
use Walnut\Lang\Blueprint\Program\Registry\MethodRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\Expression\ConstantExpression;
use Walnut\Lang\Implementation\Code\Expression\MatchExpression;
use Walnut\Lang\Implementation\Code\Expression\MatchExpressionDefault;
use Walnut\Lang\Implementation\Code\Expression\MatchExpressionPair;
use Walnut\Lang\Implementation\Code\Expression\MethodCallExpression;
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
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
		private MethodRegistry $methodRegistry
	) {}

	public function constant(Value $value): ConstantExpression {
		return new ConstantExpression($this->typeRegistry, $value);
	}

	/** @param list<Expression> $values */
	public function tuple(array $values): TupleExpression {
		return new TupleExpression($this->typeRegistry, $this->valueRegistry, $values);
	}
	/** @param array<string, Expression> $values */
	public function record(array $values): RecordExpression {
		return new RecordExpression($this->typeRegistry, $this->valueRegistry, $values);
	}

	/** @param list<Expression> $values */
	public function sequence(array $values): SequenceExpression {
		return new SequenceExpression($this->typeRegistry, $this->valueRegistry, $values);
	}

	public function return(Expression $returnedExpression): ReturnExpression {
		return new ReturnExpression($this->typeRegistry, $returnedExpression);
	}
	public function noError(Expression $targetExpression): NoErrorExpression {
		return new NoErrorExpression($this->typeRegistry, $targetExpression);
	}
	public function noExternalError(Expression $targetExpression): NoExternalErrorExpression {
		return new NoExternalErrorExpression($this->typeRegistry, $targetExpression);
	}
	public function variableName(VariableNameIdentifier $variableName): VariableNameExpression {
		return new VariableNameExpression($this->typeRegistry, $variableName);
	}
	public function variableAssignment(
		VariableNameIdentifier $variableName,
		Expression $assignedExpression
	): VariableAssignmentExpression {
		return new VariableAssignmentExpression(
			$this->typeRegistry,
			$variableName,
			$assignedExpression
		);
	}

	/** @param list<MatchExpressionPairInterface|MatchExpressionDefaultInterface> $pairs */
	public function match(
		Expression $target,
		MatchExpressionOperation $operation,
		array $pairs
	): MatchExpression {
		return new MatchExpression(
			$this->typeRegistry,
			$this->valueRegistry,
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
			$this->typeRegistry,
			$this->methodRegistry,
			$target,
			$methodName,
			$parameter
		);
	}

	public function functionBody(Expression $expression): FunctionBody {
		return new FunctionBody($this->typeRegistry, $expression);
	}

}