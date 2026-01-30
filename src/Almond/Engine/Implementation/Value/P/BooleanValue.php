<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Value\P;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Type\BooleanType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\FalseType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\TrueType;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Blueprint\Value\BooleanValue as BooleanValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

final class BooleanValue implements BooleanValueInterface, JsonSerializable {

    public function __construct(
	    public readonly TrueType|FalseType $type,
		public readonly EnumerationValueName $name,
	    public readonly bool $literalValue
    ) {}

	public BooleanType $enumeration { get => $this->type->enumeration; }

	public function validate(ValidationRequest $request): ValidationResult {
		return $request->ok();
	}

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		return $dependencyContext;
	}

	public function equals(Value $other): bool {
		return $other instanceof BooleanValueInterface && $this->literalValue === $other->literalValue;
	}

	public function __toString(): string {
		return $this->literalValue ? 'true' : 'false';
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'Boolean',
			'value' => $this->literalValue ? 'true' : 'false'
		];
	}
}