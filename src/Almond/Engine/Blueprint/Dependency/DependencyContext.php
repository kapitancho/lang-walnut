<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Dependency;

use Walnut\Lang\Almond\Engine\Blueprint\Function\UserlandFunction;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationError;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;

interface DependencyContext {
	/** @var list<ValidationError> $errors */
	public array $errors { get; }

	public function withCheckForType(Type $type, UserlandFunction $origin): DependencyContext;
	public function mergeWith(DependencyContext $other): DependencyContext;

	public function asValidationResult(): ValidationResult;
}