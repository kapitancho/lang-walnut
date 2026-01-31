<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Value\BuiltIn;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue as ErrorValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;

final class ErrorValue implements ErrorValueInterface, JsonSerializable {
    public function __construct(
        private readonly TypeRegistry $typeRegistry,
        public readonly Value $errorValue
    ) {}

	public ResultType $type {
        get => $this->typeRegistry->result(
            $this->typeRegistry->nothing,
            $this->errorValue->type
        );
    }

    public function equals(Value $other): bool {
        return $other instanceof self && $this->errorValue->equals($other->errorValue);
    }

	public function validate(ValidationRequest $request): ValidationResult {
		return $this->errorValue->validate($request);
	}

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		return $this->errorValue->validateDependencies($dependencyContext);
	}

    public function __toString(): string {
        return sprintf("@%s", $this->errorValue);
    }

	public function jsonSerialize(): array {
		return [
			'valueType' => 'Error',
			'errorValue' => $this->errorValue
		];
	}

}