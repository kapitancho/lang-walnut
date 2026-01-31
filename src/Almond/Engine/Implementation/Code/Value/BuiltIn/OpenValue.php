<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Value\BuiltIn;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\OpenValue as OpenValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\IncompatibleValueType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\DependencyContainer\DependencyContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;

final readonly class OpenValue implements OpenValueInterface, JsonSerializable {

    public function __construct(
	    public OpenType $type,
	    public Value    $value
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
		if ($type instanceof OpenType) {
			if (!$this->value->type->isSubtypeOf($type->valueType)) {
				return $request->withError(
					ValidationErrorType::valueTypeMismatch,
					sprintf(
						'The value of the open type %s should be a subtype of %s but got %s',
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
					'The type %s is not an open type',
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
		return $other instanceof OpenValueInterface &&
			$this->type->name->equals($other->type->name) &&
			$this->value->equals($other->value);
	}

	public function __toString(): string {
		$sv = (string)$this->value;
		return sprintf(
			str_starts_with($sv, '[') ? "%s%s" : "%s{%s}",
			$this->type->name,
			$sv
		);
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'Open',
			'typeName' => $this->type->name,
			'value' => $this->value
		];
	}
}