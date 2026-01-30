<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Value\P;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Type\EnumerationSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\EnumerationType;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Blueprint\Value\EnumerationValue as EnumerationValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

final class EnumerationValue implements EnumerationValueInterface, JsonSerializable {

    public function __construct(
	    public readonly EnumerationType $enumeration,
        public readonly EnumerationValueName $name
    ) {}

	public EnumerationSubsetType $type {
        get => $this->enumeration->subsetType([$this->name]);
    }

	public function validate(ValidationRequest $request): ValidationResult {
		return $request->ok();
	}

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		return $dependencyContext;
	}

	public function equals(Value $other): bool {
		return $other instanceof EnumerationValueInterface &&
			$this->enumeration->name->equals($other->enumeration->name) &&
			$this->name->equals($other->name);
	}

	public function __toString(): string {
		return sprintf(
			"%s.%s",
			$this->enumeration->name,
			$this->name
		);
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'EnumerationValue',
			'typeName' => $this->enumeration->name,
			'valueIdentifier' => $this->name
		];
	}
}