<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\MethodContext;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;

final readonly class MethodCallExpression implements Expression, JsonSerializable {

	public function __construct(
		private TypeRegistry $typeRegistry,
		private MethodContext $methodContext,

		public Expression $target,
		public MethodName $methodName,
		public Expression $parameter,
	) {}

	public function validateInContext(ValidationContext $validationContext): ValidationContext|ValidationFailure {
		$failure = null;

		$targetResult = $this->target->validateInContext($validationContext);
		if ($targetResult instanceof ValidationContext) {
			$validationContext = $targetResult;
		} else {
			if (!$this->target->isScopeSafe()) {
				return $targetResult;
			}
			$failure = $targetResult;
		}
		$targetExpressionType = $validationContext->expressionType;
		$targetReturnType = $validationContext->returnType;

		$parameterResult = $this->parameter->validateInContext($validationContext);
		if ($parameterResult instanceof ValidationContext) {
			$validationContext = $parameterResult;
		} else {
			if (!$this->parameter->isScopeSafe()) {
				return $parameterResult;
			}
			$failure = $failure === null ? $parameterResult : $failure->mergeFailure($parameterResult);
		}

		if ($failure !== null) {
			return $failure;
		}

		$parameterExpressionType = $validationContext->expressionType;
		$parameterReturnType = $validationContext->returnType;

		$validationResult = $this->methodContext->validateMethod(
			$targetExpressionType,
			$this->methodName,
			$parameterExpressionType,
			$this
		);
		if ($validationResult instanceof ValidationFailure) {
			return $validationResult;
		}
		$returnType = $this->typeRegistry->union([
			$targetReturnType,
			$parameterReturnType
		]);
		return $validationContext
			->withExpressionType($validationResult->type)
			->withReturnType($returnType);
	}

	public function isScopeSafe(): bool {
		return $this->target->isScopeSafe() && $this->parameter->isScopeSafe();
	}

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		return $dependencyContext
			|> $this->target->validateDependencies(...)
			|> $this->parameter->validateDependencies(...);
	}

	public function execute(ExecutionContext $executionContext): ExecutionContext {
		$executionContext = $this->target->execute($executionContext);
		$targetValue = $executionContext->value;

		$executionContext = $this->parameter->execute($executionContext);
		$parameterValue = $executionContext->value;

		try {
			$returnValue = $this->methodContext->executeMethod(
				$targetValue,
				$this->methodName,
				$parameterValue
			);
			return $executionContext->withValue($returnValue);
			// @codeCoverageIgnoreStart
		} catch (ExecutionException $e) {
			throw new ExecutionException(
				sprintf("Execution error in method call %s->%s(%s) : \n %s",
					$this->target,
					$this->methodName,
					$this->parameter,
					$e->getMessage()
				),
				previous: $e
			);
		}
		// @codeCoverageIgnoreEnd
	}

	public function __toString(): string {
		$parameter = (string)$this->parameter;
		if (!($parameter[0] === '[' && $parameter[-1] === ']')) {
			$parameter = "($parameter)";
		}
		return sprintf(
			"%s->%s%s",
			$this->target,
			$this->methodName,
			$parameter === '(null)' ? '' : $parameter
		);
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'MethodCall',
			'target' => $this->target,
			'methodName' => $this->methodName,
			'parameter' => $this->parameter
		];
	}
}