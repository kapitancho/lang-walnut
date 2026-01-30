<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Expression;

use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFailure;

final readonly class GroupExpression implements Expression {

	public function __construct(private Expression $innerExpression) {}

	public function validateInContext(ValidationContext $validationContext): ValidationContext|ValidationFailure {
		return $this->innerExpression->validateInContext($validationContext);
	}

	public function isScopeSafe(): bool { return $this->innerExpression->isScopeSafe(); }

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		return $this->innerExpression->validateDependencies($dependencyContext);
	}

	public function execute(ExecutionContext $executionContext): ExecutionContext {
		return $this->innerExpression->execute($executionContext);
	}

	public function __toString(): string {
		return sprintf("(%s)", $this->innerExpression);
	}

	public function jsonSerialize(): array {
		return [
			'expressionType' => 'group',
			'innerExpression' => $this->innerExpression
		];
	}
}