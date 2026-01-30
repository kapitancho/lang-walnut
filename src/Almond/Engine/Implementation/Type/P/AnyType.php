<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Type\P;

use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Type\AnyType as AnyTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;

final readonly class AnyType implements AnyTypeInterface, SupertypeChecker {

	public function isSubtypeOf(Type $ofType): bool {
		return match(true) {
			$ofType instanceof AnyTypeInterface => true,
			$ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
			default => false
		};
	}

	public function hydrate(HydrationRequest $request): HydrationSuccess|HydrationFailure {
		return $request->ok($request->value);
	}

	public function isSupertypeOf(Type $ofType): bool {
		return true;
	}

	public function __toString(): string {
		return 'Any';
	}

	public function validate(ValidationRequest $request): ValidationResult {
		return $request->ok();
	}

	public function jsonSerialize(): array {
		return ['type' => 'Any'];
	}

}