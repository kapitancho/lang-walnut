<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Value\BuiltIn;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue as TypeValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;

final class TypeValue implements TypeValueInterface, JsonSerializable {

    public function __construct(
		private readonly TypeRegistry $typeRegistry,
		public readonly Type $typeValue
    ) {}

	public TypeType $type {
		get => $this->typeRegistry->type($this->typeValue);
    }

	public function equals(Value $other): bool {
		return $other instanceof TypeValueInterface &&
			$this->typeValue->isSubtypeOf($other->typeValue) &&
			$other->typeValue->isSubtypeOf($this->typeValue);
	}

	public function validate(ValidationRequest $request): ValidationResult {
		return $this->typeValue->validate($request);
	}

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		return $dependencyContext;
	}

	public function __toString(): string {
		$val = (string)$this->typeValue;
		return sprintf(
			"type%s",
			str_starts_with($val, '[') && str_ends_with($val, ']') ?
				$val : '{' . $val . '}'
		);
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'Type',
			'value' => $this->typeValue
		];
	}
}