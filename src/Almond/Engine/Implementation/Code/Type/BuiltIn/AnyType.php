<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AnyType as AnyTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;

final readonly class AnyType implements AnyTypeInterface, JsonSerializable, SupertypeChecker {

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