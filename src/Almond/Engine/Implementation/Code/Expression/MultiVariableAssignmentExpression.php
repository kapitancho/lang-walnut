<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Expression;

use BcMath\Number;
use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\MethodContext;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;

final readonly class MultiVariableAssignmentExpression implements Expression, JsonSerializable {

	/** @param array<VariableName> $variableNames */
	public function __construct(
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
		private MethodContext $methodContext,

		public array $variableNames,
		public Expression $assignedExpression
	) {}

	public function validateInContext(ValidationContext $validationContext): ValidationContext|ValidationFailure {
		$methodName = new MethodName('item');
		$ret = $this->assignedExpression->validateInContext($validationContext);
		if ($ret instanceof ValidationFailure) {
			return $ret;
		}
		$retType = $ret->expressionType;
		$isList = array_is_list($this->variableNames);
		foreach ($this->variableNames as $key => $variableName) {
			$itemValidationResult = $this->methodContext->validateMethod(
				$retType,
				$methodName,
				($isList ?
					$this->typeRegistry->integerSubset([new Number($key)]) :
					$this->typeRegistry->stringSubset([$key])
				),
				$this
			);
			if ($itemValidationResult instanceof ValidationFailure) {
				return $itemValidationResult;
			}
			$ret = $ret->withAddedVariableType(
				$variableName,
				$itemValidationResult->type
			);
		}
		return $ret;
	}

	public function isScopeSafe(): bool { return false; }

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		return $this->assignedExpression->validateDependencies($dependencyContext);
	}

	public function execute(ExecutionContext $executionContext): ExecutionContext {
		$methodName = new MethodName('item');
		$ret = $this->assignedExpression->execute($executionContext);
		$val = $ret->value;
		$isList = array_is_list($this->variableNames);

		foreach ($this->variableNames as $key => $variableName) {
			$ret = $ret->withAddedVariableValue(
				$variableName,
				$this->methodContext->executeMethod(
					$val,
					$methodName,
					($isList ?
						$this->valueRegistry->integer($key) :
						$this->valueRegistry->string($key)
					)
				)
			);
		}
		return $ret;
	}

	public function __toString(): string {
		$variableNames = [];
		if (array_is_list($this->variableNames)) {
			foreach($this->variableNames as $variableName) {
				$variableNames[] = $variableName;
			}
		} else {
			foreach($this->variableNames as $key => $variableName) {
				$variableNames[] = $variableName->identifier === $key ?
					"~$key" : "$key: $variableName";
			}
		}
		return sprintf(
			"var{%s} = %s",
			implode(', ', $variableNames),
			$this->assignedExpression
		);
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'MultiVariableAssignment',
			'variableNames' => $this->variableNames,
			'assignedExpression' => $this->assignedExpression
		];
	}
}