<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType as StringTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\LengthRange;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;

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