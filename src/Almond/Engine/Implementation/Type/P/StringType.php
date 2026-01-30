<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Type\P;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Range\LengthRange;
use Walnut\Lang\Almond\Engine\Blueprint\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\StringType as StringTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Blueprint\Value\StringValue;

final readonly class StringType implements StringTypeInterface, JsonSerializable {

	public function __construct(
		public LengthRange $range
    ) {}

	public function hydrate(HydrationRequest $request): HydrationSuccess|HydrationFailure {
		if ($request->value instanceof StringValue) {
			$l = mb_strlen($request->value->literalValue);
			if ($this->range->minLength <= $l && (
					$this->range->maxLength === PlusInfinity::value ||
					$this->range->maxLength >= $l
				)) {
				return $request->ok($request->value);
			}
			return $request->withError(
				sprintf("The string value should be with a length between %s and %s",
					$this->range->minLength,
					$this->range->maxLength === PlusInfinity::value ? "+Infinity" : $this->range->maxLength,
				),
				$this
			);
		}
		return $request->withError(
			sprintf("The value should be a string with a length between %s and %s",
				$this->range->minLength,
				$this->range->maxLength === PlusInfinity::value ? "+Infinity" : $this->range->maxLength,
			),
			$this
		);
	}

	public function isSubtypeOf(Type $ofType): bool {
		return match(true) {
			$ofType instanceof StringSubsetType =>
				$this->range->maxLength !== PlusInfinity::value && (string)$this->range->maxLength === '0' &&
				count($ofType->subsetValues) === 1 && $ofType->subsetValues[0] === '',
			$ofType instanceof StringTypeInterface => $this->range->isSubRangeOf($ofType->range),
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
		return sprintf("String%s", $range === '..' ? '' : "<$range>");
	}

	public function validate(ValidationRequest $request): ValidationResult {
		return $request->ok();
	}

	public function jsonSerialize(): array {
		return ['type' => 'String', 'range' => $this->range];
	}


}