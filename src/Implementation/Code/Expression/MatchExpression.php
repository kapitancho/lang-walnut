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
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\BooleanValue;
use Walnut\Lang\Blueprint\Value\NullValue;
use Walnut\Lang\Blueprint\Value\TypeValue;

final readonly class MatchExpression implements MatchExpressionInterface, JsonSerializable {

	/** @param list<MatchExpressionPair|MatchExpressionDefault> $pairs */
	public function __construct(
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
							$analyserContext->programRegistry->typeRegistry->intersection([
								$matchResult->expressionType->refType,
								$innerContext->variableScope->typeOf($this->target->variableName),
							])
						);
					}
					if ($this->operation instanceof MatchExpressionEquals) {
						$innerContext = $innerContext->withAddedVariableType(
							$this->target->variableName,
							$analyserContext->programRegistry->typeRegistry->intersection([
								$matchResult->expressionType,
								$innerContext->variableScope->typeOf($this->target->variableName),
							])
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
				$analyserContext->programRegistry->typeRegistry->union($refTypes)
			)) {
				$expressionTypes[] = $analyserContext->programRegistry->typeRegistry->null;
			}
		}
		return $retTarget->asAnalyserResult(
			$analyserContext->programRegistry->typeRegistry->union($expressionTypes),
			$analyserContext->programRegistry->typeRegistry->union($returnTypes)
		);
	}

	/** @return list<string> */
	public function analyseDependencyType(DependencyContainer $dependencyContainer): array {
		return array_merge(
			$this->target->analyseDependencyType($dependencyContainer),
			... array_map(
				fn(MatchExpressionPair|MatchExpressionDefault $pair) => match(true) {
					$pair instanceof MatchExpressionPair =>
					array_merge(
						$pair->matchExpression->analyseDependencyType($dependencyContainer),
						$pair->valueExpression->analyseDependencyType($dependencyContainer)
					),
					$pair instanceof MatchExpressionDefault =>
					$pair->valueExpression->analyseDependencyType($dependencyContainer)
				},
				$this->pairs
			)
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
					if ($this->operation instanceof MatchExpressionIsSubtypeOf && $innerContext->value instanceof TypeValue) {
						$typedValue = $innerContext->variableValueScope->typedValueOf($this->target->variableName);
						$innerContext = $innerContext->withAddedVariableValue(
							$this->target->variableName,
							$typedValue
						);
					}
					if ($this->operation instanceof MatchExpressionEquals) {
						$innerContext = $innerContext->withAddedVariableValue(
							$this->target->variableName,
							$innerContext->value
						);
					}
				}
				$result = $pair->valueExpression->execute($innerContext);
				return $executionContext->withValue($result->value);
			}
		}
		return $executionContext->asExecutionResult(
			$executionContext->programRegistry->valueRegistry->null
		);
	}

	public function __toString(): string {
		$isMatchTrue = $this->target instanceof ConstantExpression &&
			$this->target->value instanceof BooleanValue &&
			$this->target->value->literalValue;

		$isIf = !$isMatchTrue && count($this->pairs) === 2 &&
			$this->pairs[0] instanceof MatchExpressionPair &&
			$this->pairs[1] instanceof MatchExpressionDefault &&
			$this->pairs[0]->matchExpression instanceof ConstantExpression &&
			$this->pairs[0]->matchExpression->value instanceof BooleanValue &&
			$this->pairs[0]->matchExpression->value->literalValue &&
			$this->target instanceof MethodCallExpression &&
			$this->target->methodName->equals(new MethodNameIdentifier('asBoolean'));
		$else = !$isIf || ($this->pairs[1]->valueExpression instanceof ConstantExpression &&
			$this->pairs[1]->valueExpression->value instanceof NullValue) ? '' :
			sprintf(" ~ { %s }", $this->pairs[1]->valueExpression);

		$pairs = implode(", ", $this->pairs);

		return match(true) {
			$this->operation instanceof MatchExpressionIsSubtypeOf =>
				sprintf("?whenTypeOf (%s) is { %s }", $this->target, $pairs),
			$isMatchTrue =>
				sprintf("?whenIsTrue { %s }", $pairs),
			$isIf =>
				sprintf("?when (%s) { %s }%s", $this->target->target, $this->pairs[0]->valueExpression, $else),
			default =>
				sprintf("?whenValueOf (%s) is { %s }", $this->target, $pairs),
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