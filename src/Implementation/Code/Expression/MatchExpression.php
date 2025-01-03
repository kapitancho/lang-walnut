<?php

namespace Walnut\Lang\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserResult;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionContext;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionResult;
use Walnut\Lang\Blueprint\Code\Expression\ConstantExpression;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpression as MatchExpressionInterface;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpressionDefault;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpressionOperation;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpressionPair;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\TypeValue;

final readonly class MatchExpression implements MatchExpressionInterface, JsonSerializable {

	/** @param list<MatchExpressionPair|MatchExpressionDefault> $pairs */
	public function __construct(
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
		public Expression $target,
		public MatchExpressionOperation $operation,
		public array $pairs
	) {}

	public function analyse(AnalyserContext $analyserContext): AnalyserResult {
		$retTarget = $this->target->analyse($analyserContext);

		$expressionTypes = [];
		$returnTypes = [$retTarget->returnType];
		$hasDefaultMatch = false;
		$hasDynamicTypes = false;

		$refTypes = [];

		foreach($this->pairs as $pair) {
			$innerContext = $retTarget;

			if ($pair instanceof MatchExpressionPair) {
				$matchExpression = $pair->matchExpression;
				$matchResult = $matchExpression->analyse($innerContext);

				if ($this->operation instanceof MatchExpressionIsSubtypeOf && $matchResult->expressionType instanceof TypeType) {
					if ($matchExpression instanceof ConstantExpression && $matchExpression->value instanceof TypeValue) {
						$refTypes[] = $matchExpression->value->typeValue;
					} else {
						$hasDynamicTypes = true;
					}
				}
				if ($this->operation instanceof MatchExpressionEquals) {
					if ($matchExpression instanceof ConstantExpression) {
						$refTypes[] = $matchExpression->value->type;
					} else {
						$hasDynamicTypes = true;
					}
				}

				if ($this->target instanceof VariableNameExpression) {
					if ($this->operation instanceof MatchExpressionIsSubtypeOf && $matchResult->expressionType instanceof TypeType) {
						$innerContext = $innerContext->withAddedVariableType(
							$this->target->variableName,
							$matchResult->expressionType->refType,
						);
					}
					if ($this->operation instanceof MatchExpressionEquals) {
						$innerContext = $innerContext->withAddedVariableType(
							$this->target->variableName,
							$matchResult->expressionType,
						);
					}
				}
			} else {
				$hasDefaultMatch = true;
			}
			$retValue = $pair->valueExpression->analyse($innerContext);

			$expressionTypes[] = $retValue->expressionType;
			$returnTypes[] = $retValue->returnType;
		}
		if (!$hasDefaultMatch) {
			if ($hasDynamicTypes || !$retTarget->expressionType->isSubtypeOf(
				$this->typeRegistry->union($refTypes)
			)) {
				$expressionTypes[] = $this->typeRegistry->null;
			}
		}
		return $retTarget->asAnalyserResult(
			$this->typeRegistry->union($expressionTypes),
			$this->typeRegistry->union($returnTypes)
		);
	}

	public function execute(ExecutionContext $executionContext): ExecutionResult {
		$executionContext = $this->target->execute($executionContext);

		foreach($this->pairs as $pair) {
			if ($pair instanceof MatchExpressionDefault) {
				return $pair->valueExpression->execute($executionContext);
			}
			$innerContext = $pair->matchExpression->execute($executionContext);
			if ($this->operation->match($executionContext->value, $innerContext->value)) {
				if ($this->target instanceof VariableNameExpression) {
					if ($this->operation instanceof MatchExpressionIsSubtypeOf && ($type = $innerContext->value) instanceof TypeValue) {
						$innerContext = $innerContext->withAddedVariableValue(
							$this->target->variableName,
							new TypedValue(
								$type->typeValue,
								$innerContext->variableValueScope->valueOf($this->target->variableName)
							)
						);
					}
					if ($this->operation instanceof MatchExpressionEquals) {
						$innerContext = $innerContext->withAddedVariableValue(
							$this->target->variableName,
							$innerContext->typedValue,
						);
					}
				}
				return $pair->valueExpression->execute($innerContext);
			}
		}
		return $executionContext->asExecutionResult(
			TypedValue::forValue($this->valueRegistry->null)
		);
	}

	public function __toString(): string {
		$isMatchTrue = $this->target instanceof ConstantExpression &&
			$this->target->value->equals($this->valueRegistry->true);

		$pairs = implode(", ", $this->pairs);

		return match(true) {
			$this->operation instanceof MatchExpressionIsSubtypeOf =>
				sprintf("?whenTypeOf (%s) %s { %s }", $this->target, $this->operation, $pairs),
			$isMatchTrue =>
				sprintf("?whenIsTrue { %s }", $pairs),
			default =>
				sprintf("?whenValueOf (%s) %s { %s }", $this->target, $this->operation, $pairs),
		};
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'Match',
			'target' => $this->target,
			'operation' => $this->operation,
			'pairs' => $this->pairs
		];
	}
}