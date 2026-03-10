<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Value\BuiltIn;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ValueType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ValueValue as WrappedValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;

final class ValueValue implements WrappedValueInterface, JsonSerializable {
	public function __construct(
		private readonly TypeRegistry $typeRegistry,
		public readonly Value $value
	) {}

	public ValueType $type {
		get => $this->typeRegistry->valueType($this->value->type);
	}

	public function equals(Value $other): bool {
		return $other instanceof self && $this->value->equals($other->value);
	}

	public function validate(ValidationRequest $request): ValidationResult {
		return $this->value->validate($request);
	}

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		return $this->value->validateDependencies($dependencyContext);
	}

	public function __toString(): string {
		return sprintf("Value(%s)", $this->value);
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'Wrapped',
			'wrappedValue' => $this->value
		];
	}
}
