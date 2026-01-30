<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Range\LengthRange;
use Walnut\Lang\Almond\Engine\Blueprint\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Type\BytesType as BytesTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Blueprint\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;

final readonly class BytesType implements BytesTypeInterface, JsonSerializable {

	public function __construct(public LengthRange $range) {}

	public function hydrate(HydrationRequest $request): HydrationSuccess|HydrationFailure {
		return $request->namedTypeHydrator->tryHydrateByName($this, $request) ??
			$request->withError('Cannot hydrate Bytes type directly', $this);
	}

	public function isSubtypeOf(Type $ofType): bool {
		return match(true) {
			$ofType instanceof BytesTypeInterface => $this->range->isSubRangeOf($ofType->range),
			$ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
			default => false
		};
	}

	public function __toString(): string {
		$range = (string)$this->range;
		if (
			$this->range->maxLength !== PlusInfinity::value &&
			(string)$this->range->minLength === (string)$this->range->maxLength
		) {
			$range = (string)$this->range->minLength;
		}
		return sprintf("Bytes%s", $range === '..' ? '' : "<$range>");
	}

	public function validate(ValidationRequest $request): ValidationResult {
		return $request->ok();
	}

	public function jsonSerialize(): array {
		return ['type' => 'Bytes', 'range' => $this->range];
	}
}
