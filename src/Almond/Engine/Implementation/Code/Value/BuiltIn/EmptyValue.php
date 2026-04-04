<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Value\BuiltIn;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AtomType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\EmptyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\AtomValue as AtomValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\EmptyValue as EmptyValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;

final readonly class EmptyValue implements EmptyValueInterface, AtomValueInterface, JsonSerializable {

	public function __construct(
		public EmptyType&AtomType $type,
	) {}

	public function equals(Value $other): bool {
		return $other instanceof EmptyValueInterface;
	}

	public function validate(ValidationRequest $request): ValidationResult {
		return $request->ok();
	}

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		return $dependencyContext;
	}

	public function __toString(): string {
		return 'empty';
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'Empty'
		];
	}
}