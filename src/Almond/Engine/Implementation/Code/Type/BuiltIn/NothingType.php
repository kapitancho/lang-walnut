<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType as NothingTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;

final readonly class NothingType implements NothingTypeInterface, JsonSerializable {

	public function isSubtypeOf(Type $ofType): bool {
		return true;
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