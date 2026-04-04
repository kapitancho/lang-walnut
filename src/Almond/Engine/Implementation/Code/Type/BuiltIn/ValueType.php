<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AnyType as AnyTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ValueType as ValueTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;

final readonly class ValueType implements ValueTypeInterface, JsonSerializable {

	public function __construct(
		public Type $valueType
	) {}

	public function hydrate(HydrationRequest $request): HydrationSuccess|HydrationFailure {
		$result = $this->valueType->hydrate($request);
		if ($result instanceof HydrationSuccess) {
			return $request->ok(
				$request->valueRegistry->value($request->value)
			);
		}
		return $result;
	}

	public function isSubtypeOf(Type $ofType): bool {
		return match(true) {
			$ofType instanceof ValueTypeInterface =>
				$this->valueType->isSubtypeOf($ofType->valueType),
			$ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
			default => false
		};
	}

	public function __toString(): string {
		return $this->valueType instanceof AnyTypeInterface ?
			'Value' : sprintf("Value<%s>", $this->valueType);
	}

	public function validate(ValidationRequest $request): ValidationResult {
		return $this->valueType->validate($request);
	}

	public function jsonSerialize(): array {
		return ['type' => 'Value', 'valueType' => $this->valueType];
	}
}
