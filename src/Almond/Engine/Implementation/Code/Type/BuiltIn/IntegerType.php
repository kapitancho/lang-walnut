<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn;

use BcMath\Number;
use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType as IntegerTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType as RealTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberRange;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;

final readonly class IntegerType implements IntegerTypeInterface, JsonSerializable {
	public function __construct(
		public NumberRange $numberRange,
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
