<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Expression;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;

final readonly class GroupExpression implements Expression, JsonSerializable {

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
			'expressionType' => 'Group',
			'innerExpression' => $this->innerExpression
		];
	}
}