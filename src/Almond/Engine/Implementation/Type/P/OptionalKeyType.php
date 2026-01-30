<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Type\P;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Type\OptionalKeyType as OptionalKeyTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;

final class OptionalKeyType implements OptionalKeyTypeInterface, SupertypeChecker, JsonSerializable {

    public function __construct(
        public readonly Type $valueType
    ) {}

	public function hydrate(HydrationRequest $request): HydrationSuccess|HydrationFailure {
		return $this->valueType->hydrate($request);
	}

	public function isSubtypeOf(Type $ofType): bool {
		return match(true) {
			$ofType instanceof OptionalKeyTypeInterface => $this->valueType->isSubtypeOf($ofType->valueType),
			$ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
			default => false
		};
	}

	public function isSupertypeOf(Type $ofType): bool {
		return $ofType->isSubtypeOf($this->valueType);
	}

	public function __toString(): string {
		return sprintf("OptionalKey<%s>", $this->valueType);
	}

	public function validate(ValidationRequest $request): ValidationResult {
		return $this->valueType->validate($request);
	}

	public function jsonSerialize(): array {
		return ['type' => 'OptionalKey', 'valueType' => $this->valueType];
	}
}