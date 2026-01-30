<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Type\P;

use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Type\NothingType as NothingTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Implementation\Type\Missing\MissingType;

final readonly class NothingType implements NothingTypeInterface {

	public function isSubtypeOf(Type $ofType): bool {
		return !$ofType instanceof MissingType;
	}

	public function __toString(): string {
		return 'Nothing';
	}

	public function hydrate(HydrationRequest $request): HydrationSuccess|HydrationFailure {
		return $request->withError(
			sprintf("There is no value allowed, %s provided", $request->value),
			$this
		);
	}

	public function validate(ValidationRequest $request): ValidationResult {
		return $request->ok();
	}

	public function jsonSerialize(): array {
		return ['type' => 'Nothing'];
	}

}