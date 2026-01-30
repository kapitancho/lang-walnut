<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Value;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Type\AtomType;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Blueprint\Value\AtomValue as AtomValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

final readonly class AtomValue implements AtomValueInterface, JsonSerializable {

    public function __construct(public AtomType $type) {}

	public function validate(ValidationRequest $request): ValidationResult {
		return $request->ok();
	}

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		return $dependencyContext;
	}

	public function equals(Value $other): bool {
		return $other instanceof AtomValueInterface && $this->type->name->equals($other->type->name);
	}

	public function __toString(): string {
		return $this->type;
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'Atom',
			'typeName' => $this->type->name
		];
	}
}