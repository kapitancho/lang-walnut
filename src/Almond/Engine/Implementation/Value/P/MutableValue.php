<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Value\P;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Blueprint\Value\MutableValue as MutableValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

final class MutableValue implements MutableValueInterface, JsonSerializable {

    public function __construct(
		private readonly TypeRegistry $typeRegistry,
		public readonly Type $targetType,
	    public Value $value
    ) {}

	public Type $type { get => $this->type ??= $this->typeRegistry->mutable($this->targetType); }

	public function validate(ValidationRequest $request): ValidationResult {
		$targetType = $this->targetType;
		$request = $request |> $this->value->validate(...);

		if (!$this->value->type->isSubtypeOf($targetType)) {
			$request = $request->withError(
				ValidationErrorType::valueTypeMismatch,
				sprintf(
					'The value of the mutable type should be a subtype of %s but got %s',
					$this->targetType,
					$this->value->type,
				),
				$this
			);
		}
		return $request;
	}

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		return $this->value->validateDependencies($dependencyContext);
	}

	public function equals(Value $other): bool {
		return $other instanceof MutableValueInterface &&
			$this->targetType->isSubtypeOf($other->targetType) &&
			$other->targetType->isSubtypeOf($this->targetType) &&
			$this->value->equals($other->value);
	}

	public function __toString(): string {
		return sprintf(
			"mutable{%s, %s}",
			$this->targetType,
			$this->value
		);
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'Mutable',
			'targetType' => $this->targetType,
			'value' => $this->value
		];
	}
}