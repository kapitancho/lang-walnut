<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Value\P;

use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Type\AtomType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Blueprint\Value\AtomValue as AtomValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Value\NullValue as NullValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

final readonly class NullValue implements NullValueInterface, AtomValueInterface {

	public function __construct(
		public NullType&AtomType $type,
	) {}

	public function equals(Value $other): bool {
		return $other instanceof NullValueInterface;
	}

	public function validate(ValidationRequest $request): ValidationResult {
		return $request->ok();
	}

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		return $dependencyContext;
	}

	public function __toString(): string {
		return 'null';
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'Null'
		];
	}
}