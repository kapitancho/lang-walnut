<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Value\P;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Dependency\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Type\DataType;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Blueprint\Value\DataValue as DataValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

final readonly class DataValue implements DataValueInterface, JsonSerializable {

    public function __construct(
	    public DataType $type,
	    public Value    $value
    ) {}

	public function validate(ValidationRequest $request): ValidationResult {
		if (!$this->value->type->isSubtypeOf($this->type->valueType)) {
			return $request->withError(
				ValidationErrorType::valueTypeMismatch,
				sprintf(
					'The value of the data type %s should be a subtype of %s but got %s',
					$this->type,
					$this->type->valueType,
					$this->value->type,
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
		return $other instanceof DataValueInterface &&
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
			'valueType' => 'Data',
			'typeName' => $this->type->name,
			'value' => $this->value
		];
	}
}