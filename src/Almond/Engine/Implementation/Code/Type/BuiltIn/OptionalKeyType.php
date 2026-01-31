<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OptionalKeyType as OptionalKeyTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;

final readonly class OptionalKeyType implements OptionalKeyTypeInterface, SupertypeChecker, JsonSerializable {

    public function __construct(
        public Type $valueType
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