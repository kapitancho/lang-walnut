<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Type;

use BcMath\Number;
use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Range\NumberRange as NumberRangeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\IntegerType as IntegerTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Blueprint\Type\RealType as RealTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Value\IntegerValue;

final readonly class IntegerType implements IntegerTypeInterface, JsonSerializable {
	public function __construct(
		public NumberRangeInterface $numberRange,
	) {}

	public function hydrate(HydrationRequest $request): HydrationSuccess|HydrationFailure {
		$value = $request->value;
		if ($value instanceof IntegerValue) {
			if ($this->contains($value->literalValue)) {
				return $request->ok($value);
			}
			return $request->withError(
				sprintf("The integer value should be in %s",
					$this->numberRange,
				),
				$this,
			);
		}
		return $request->withError(
			sprintf("The value should be an integer in %s",
				$this->numberRange,
			),
			$this,
		);
	}

	public function isSubtypeOf(Type $ofType): bool {
		return match(true) {
			$ofType instanceof IntegerTypeInterface => $ofType->numberRange->containsRange($this->numberRange),
			$ofType instanceof RealTypeInterface => $ofType->numberRange->containsRange($this->numberRange),
			$ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
			default => false
		};
	}

	public function contains(int|Number $value): bool {
		if (is_int($value)) {
			$value = new Number($value);
		}
		return $this->numberRange->contains($value);
	}

	public function __toString(): string {
		$range = (string)$this->numberRange;
		if (count($this->numberRange->intervals) === 1) {
			$range = (string)preg_replace('#((^\(-Infinity|^\[)(.*?)(\+Infinity\)$|]$))#', '$3', $range);
		}
		$range = (string)preg_replace('#[+-]Infinity#', '', $range);
		return sprintf("Integer%s", $range === '..' ? '' : "<$range>");
	}

	public function validate(ValidationRequest $request): ValidationResult {
		return $request->ok();
	}

	public function jsonSerialize(): array {
		return [
			'type' => 'Integer',
			'range' => $this->numberRange
		];
	}
}
