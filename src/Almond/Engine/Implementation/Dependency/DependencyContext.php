<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Dependency;

use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContainer;
use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContext as DependencyContextInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Function\UserlandFunction;
use Walnut\Lang\Almond\Engine\Blueprint\Type\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationError;
use Walnut\Lang\Almond\Engine\Implementation\Validation\ValidationResult;

final readonly class DependencyContext implements DependencyContextInterface {
	/**
	 * @param list<ValidationError> $errors
	 */
	public function __construct(
		private DependencyContainer $dependencyContainer,

		public array $errors
	) {}

	public function withCheckForType(Type $type, UserlandFunction $origin): DependencyContextInterface {
		$result = $type instanceof NothingType ? null : $this->dependencyContainer->checkForType($type, $origin);
		return $result ? clone($this, ['errors' => [...$this->errors, $result]]) : $this;
	}

	public function mergeWith(DependencyContextInterface $other): DependencyContextInterface {
		return clone($this, ['errors' => array_merge($this->errors, $other->errors)]);
	}

	public function asValidationResult(): ValidationResult {
		return new ValidationResult($this->errors);
	}
}