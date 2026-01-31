<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Value\BuiltIn;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BooleanType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FalseType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TrueType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue as BooleanValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;

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