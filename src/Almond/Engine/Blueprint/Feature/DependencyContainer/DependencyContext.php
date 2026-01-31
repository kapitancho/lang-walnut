<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\UserlandFunction;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationError;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;

interface DependencyContext {
	/** @var list<ValidationError> $errors */
	public array $errors { get; }

	public function withCheckForType(Type $type, UserlandFunction $origin): DependencyContext;
	public function mergeWith(DependencyContext $other): DependencyContext;

	public function asValidationResult(): ValidationResult;
}