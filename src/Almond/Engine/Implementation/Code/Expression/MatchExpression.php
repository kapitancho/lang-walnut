<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\ConstantExpression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\MatchExpressionOperation;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\MatchExpressionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;

final readonly class MatchExpression implements Expression, JsonSerializable {

	/** @param list<MatchExpressionPair> $pairs */
	public function __construct(
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,

		public MatchExpressionType $type,
		public Expression $target,
		public MatchExpressionOperation $operation,
		public array $pairs,
		public MatchExpressionDefault|null $default
	) {}

	public function validateInContext(ValidationContext $validationContext): ValidationContext|ValidationFailure {
		$validationContext = $this->target->validateInContext($validationContext);
		if ($validationContext instanceof ValidationFailure) {
			return $validationContext;
		}
		$failure = null;

		$expressionTypes = [];
		$returnTypes = [$validationContext->returnType];
		$hasDynamicTypes = false;
		$refTypes = [];


		foreach ($this->pairs as $pair) {
			$innerContext = $validationContext;
			$matchExpression = $pair->matchExpression;
			$matchResult = $matchExpression->validateInContext($innerContext);
			if ($matchResult instanceof ValidationFailure) {
				$failure = $failure ? $failure->mergeFailure($matchResult) : $matchResult;
				continue;
			}

			if ($this->type !== MatchExpressionType::typeOf) {
				if ($matchExpression instanceof ConstantExpression) {
					$refTypes[] = $matchExpression->value->type;
				} else {
					$hasDynamicTypes = true;
				}
			} elseif ($matchResult->expressionType instanceof TypeType) {
				if ($matchExpression instanceof ConstantExpression && $matchExpression->value instanceof TypeValue) {
					$refTypes[] = $matchExpression->value->typeValue;
				} else {
					$hasDynamicTypes = true;
				}
			}
			if ($this->target instanceof VariableNameExpression) {
				if ($this->type !== MatchExpressionType::typeOf) {
					$innerContext = $innerContext->withAddedVariableType(
						$this->target->variableName,
						$this->typeRegistry->intersection([
							$matchResult->expressionType,
							$innerContext->variableScope->typeOf($this->target->variableName),
						])
					);
				} elseif ($matchResult->expressionType instanceof TypeType) {
					$innerContext = $innerContext->withAddedVariableType(
						$this->target->variableName,
						$this->typeRegistry->intersection([
							$matchResult->expressionType->refType,
							$innerContext->variableScope->typeOf($this->target->variableName),
						])
					);
				}
			}
			$valueContext = $pair->valueExpression->validateInContext($innerContext);
			if ($valueContext instanceof ValidationFailure) {
				$failure = $failure ? $failure->mergeFailure($valueContext) : $valueContext;
				continue;
			}

			$expressionTypes[] = $valueContext->expressionType;
			$returnTypes[] = $valueContext->returnType;
		}
		if ($this->default) {
			$defaultContext = $this->default->valueExpression->validateInContext($validationContext);
			if ($defaultContext instanceof ValidationFailure) {
				$failure = $failure ? $failure->mergeFailure($defaultContext) : $defaultContext;
			}
			$expressionTypes[] = $defaultContext->expressionType;
			$returnTypes[] = $defaultContext->returnType;
		} elseif ($hasDynamicTypes || !$validationContext->expressionType->isSubtypeOf(
			$this->typeRegistry->union($refTypes)
		)) {
			$expressionTypes[] = $this->typeRegistry->null;
		}
		return $failure ?? $validationContext
			->withExpressionType($this->typeRegistry->union($expressionTypes))
			->withReturnType($this->typeRegistry->union($returnTypes));
	}

	public function isScopeSafe(): bool {
		return $this->target->isScopeSafe();
	}

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		$dependencyContext = $this->target->validateDependencies($dependencyContext);
		foreach ($this->pairs as $pair) {
			$dependencyContext = $pair->matchExpression->validateDependencies($dependencyContext);
		}
		if ($this->default !== null) {
			$dependencyContext = $this->default->valueExpression->validateDependencies($dependencyContext);
		}
		return $dependencyContext;
	}

	public function execute(ExecutionContext $executionContext): ExecutionContext {
		$executionContext = $this->target->execute($executionContext);
		foreach($this->pairs as $pair) {
			$innerContext = $pair->matchExpression->execute($executionContext);
			if ($this->operation->match($executionContext->value, $innerContext->value)) {
				$innerResult = $pair->valueExpression->execute($innerContext);
				return $executionContext->withValue($innerResult->value);
			}
		}
		return $this->default ? $this->default->valueExpression->execute($executionContext) :
			$executionContext->withValue($this->valueRegistry->null);
	}

	public function __toString(): string {
		$pairs = implode(", ", $this->pairs);
		$default = $this->default ? sprintf(", ~: %s", $this->default->valueExpression) : "";
		$else = $this->default ? sprintf(" ~ { %s }", $this->default->valueExpression) : "";

		return match($this->type) {
			MatchExpressionType::typeOf => sprintf(
				"?whenTypeOf (%s) { %s%s }",
				$this->target, $pairs, $default
			),
			MatchExpressionType::isTrue => sprintf(
				"?whenIsTrue { %s%s }",
				$pairs, $default
			),
			MatchExpressionType::valueOf => sprintf(
				"?whenValueOf (%s) { %s%s }",
				$this->target, $pairs, $default
			),
			MatchExpressionType::if => sprintf(
				"?when (%s) { %s }%s",
				$this->target->target,
				$this->pairs[0]->valueExpression,
				(string)$this->default->valueExpression === 'null' ? '' : $else),
		};
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'Match',
			'type' => $this->type->name,
			'target' => $this->target,
			'operation' => $this->operation,
			'pairs' => $this->pairs,
			'default' => $this->default,
		];
	}
}