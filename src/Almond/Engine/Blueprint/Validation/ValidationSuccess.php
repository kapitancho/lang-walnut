<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Validation;

use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;

interface ValidationSuccess extends ValidationResult {
	public Type $type { get; }

	/** @var array{} $errors */
	public array $errors { get; }

	public function hasErrors(): false;
}