<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\MethodContext;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TrueType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionEarlyReturn;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\Expression\Helper\BooleanExpressionHelper;

final readonly class BooleanOrExpression implements Expression, JsonSerializable {

	private BooleanExpressionHelper $booleanExpressionHelper;

	public function __construct(
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
		private MethodContext $methodContext,

		public Expression $first,
		public Expression $second,
	) {
		$this->booleanExpressionHelper = new BooleanExpressionHelper(
			$this->typeRegistry,
			$this->valueRegistry,
			$this->methodContext
		);
	}

	public function validateInContext(ValidationContext $validationContext): ValidationContext|ValidationFailure {
		return $this->booleanExpressionHelper->validateInContext(
			$validationContext,
			$this->first,
			$this->second,
			$this,
			TrueType::class
		);
	}

	/** @throws ExecutionException|ExecutionEarlyReturn */
	public function execute(ExecutionContext $executionContext): ExecutionContext {
		$firstExecutionContext = $this->first->execute($executionContext);
		$firstValue = $this->booleanExpressionHelper->getBooleanValue(
			$firstExecutionContext->value
		);

		if ($firstValue) {
			return $firstExecutionContext->withValue(
				$this->valueRegistry->boolean(true)
			);
		}
		$secondExecutionContext = $this->second->execute($firstExecutionContext);
		return $secondExecutionContext->withValue(
			$this->valueRegistry->boolean(
				$this->booleanExpressionHelper->getBooleanValue(
					$secondExecutionContext->value
				)
			)
		);
	}

	public function isScopeSafe(): bool {
		return $this->first->isScopeSafe() && $this->second->isScopeSafe();
	}

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		return $dependencyContext
			|> $this->first->validateDependencies(...)
			|> $this->second->validateDependencies(...);
	}

	public function __toString(): string {
		return sprintf(
			"(%s) || (%s)",
			$this->first,
			$this->second
		);
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'BooleanOr',
			'first' => $this->first,
			'second' => $this->second
		];
	}

}