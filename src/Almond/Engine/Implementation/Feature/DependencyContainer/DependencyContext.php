<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Feature\DependencyContainer;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\UserlandFunction;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContainer;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext as DependencyContextInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationError;
use Walnut\Lang\Almond\Engine\Implementation\Program\Validation\ValidationResult;

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