<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Expression;

use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionEarlyReturn;
use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Method\MethodContext;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Type\FalseType;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Expression\Helper\BooleanExpressionHelper;

final readonly class BooleanAndExpression implements Expression {

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
			FalseType::class
		);
	}

	/** @throws ExecutionException|ExecutionEarlyReturn */
	public function execute(ExecutionContext $executionContext): ExecutionContext {
		$firstExecutionContext = $this->first->execute($executionContext);
		$firstValue = $this->booleanExpressionHelper->getBooleanValue(
			$firstExecutionContext->value
		);

		if (!$firstValue) {
			return $firstExecutionContext->withValue(
				$this->valueRegistry->boolean(false)
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
			"(%s) && (%s)",
			$this->first,
			$this->second
		);
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'BooleanAnd',
			'first' => $this->first,
			'second' => $this->second
		];
	}

}