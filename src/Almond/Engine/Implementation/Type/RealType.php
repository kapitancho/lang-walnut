<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Type;

use BcMath\Number;
use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Range\NumberRange as NumberRangeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\RealType as RealTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Blueprint\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Value\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Value\RealValue;

final readonly class RealType implements RealTypeInterface, JsonSerializable {
	public function __construct(
		public NumberRangeInterface $numberRange,
	) {}

	public function hydrate(HydrationRequest $request): HydrationSuccess|HydrationFailure {
		$value = $request->value;
		if ($value instanceof RealValue) {
			if ($this->contains($value->literalValue)) {
				return $request->ok($value);
			}
			return $request->withError(
				sprintf("The real value should be in %s",
					$this->numberRange,
				),
				$this,
			);
		}
		return $request->withError(
			sprintf("The value should be a real in %s",
				$this->numberRange,
			),
			$this,
		);
	}

	public function isSubtypeOf(Type $ofType): bool {
		return match(true) {
			$ofType instanceof RealTypeInterface => $ofType->numberRange->containsRange($this->numberRange),
			$ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
			default => false
		};
	}

	public function contains(int|float|Number $value): bool {
		if (is_int($value)) {
			$value = new Number($value);
		} elseif (is_float($value)) {
			$value = new Number((string)$value);
		}
		return $this->numberRange->contains($value);
	}

	public function __toString(): string {
		$range = (string)$this->numberRange;
		if (count($this->numberRange->intervals) === 1) {
			$range = (string)preg_replace('#((^\(-Infinity|^\[)(.*?)(\+Infinity\)$|]$))#', '$3', $range);
		}
		$range = (string)preg_replace('#[+-]Infinity#', '', $range);
		return sprintf("Real%s", $range === '..' ? '' : "<$range>");
	}

	public function validate(ValidationRequest $request): ValidationResult {
		return $request->ok();
	}

	public function jsonSerialize(): array {
		return ['type' => 'Real', 'range' => $this->numberRange];
	}
}
