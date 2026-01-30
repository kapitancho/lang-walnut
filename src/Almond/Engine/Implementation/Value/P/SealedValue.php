<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Value\P;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Error\IncompatibleValueType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Blueprint\Value\SealedValue as SealedValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

final readonly class SealedValue implements SealedValueInterface, JsonSerializable {

    public function __construct(
	    public SealedType $type,
	    public Value      $value
    ) {
	    if (!$value->type->isSubtypeOf($type->valueType)) {
		    IncompatibleValueType::of(
			    $type,
			    $value,
		    );
	    }
    }

	public function validate(ValidationRequest $request): ValidationResult {
		$request = $this->value->validate($request);
		$type = $this->type;
		if ($type instanceof SealedType) {
			if (!$this->value->type->isSubtypeOf($type->valueType)) {
				return $request->withError(
					ValidationErrorType::valueTypeMismatch,
					sprintf(
						'The value of the sealed type %s should be a subtype of %s but got %s',
						$type,
						$type->valueType,
						$this->value->type,
					),
					$this
				);
			}
		} else {
			return $request->withError(
				ValidationErrorType::typeTypeMismatch,
				sprintf(
					'The type %s is not a sealed type',
					$this->type,
				),
				$this
			);
		}
		return $request->ok();
	}

	public function validateDependencies(DependencyContext $dependencyContext): DependencyContext {
		return $dependencyContext;
	}

	public function equals(Value $other): bool {
		return $other instanceof SealedValueInterface &&
			$this->type->name->equals($other->type->name) &&
			$this->value->equals($other->value);
	}

	public function __toString(): string {
		$sv = (string)$this->value;
		return sprintf(
			"%s!%s",
			$this->type,
			$sv
		);
	}


	public function jsonSerialize(): array {
		return [
			'valueType' => 'Sealed',
			'typeName' => $this->type->name,
			'value' => $this->value
		];
	}
}