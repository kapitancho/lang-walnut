<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Type;

use BcMath\Number;
use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Error\DuplicateSubsetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Error\InvalidArgument;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Range\NumberRange as NumberRangeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\RealSubsetType as RealSubsetTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Blueprint\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Implementation\Range\NumberInterval;
use Walnut\Lang\Almond\Engine\Implementation\Range\NumberRange;

final class RealSubsetType implements RealSubsetTypeInterface, JsonSerializable {

	private readonly RealType $underlyingType;

	/**
	 * @param list<Number> $subsetValues
	 * @throws InvalidArgument|DuplicateSubsetValue
	 */
	public function __construct(
		public readonly array $subsetValues
	) {
		if ($subsetValues === []) {
			InvalidArgument::of(
				'RealSubset[]',
				$this->subsetValues,
				"Cannot create an empty real subset type"
			);
		}
		$selected = [];
		foreach($subsetValues as $value) {
			if (!$value instanceof Number) {
				InvalidArgument::of(
					'String',
					$value,
					sprintf("Invalid value: '%s'", $value)
				);
			}
			$vs = (string)$value;
			if (!str_contains($vs, '.')) {
				$vs .= '.';
			}
			$vs = rtrim(rtrim($vs, '0'), '.');
			if (isset($selected[$vs])) {
				DuplicateSubsetValue::of(
					$this,
					$value,
				);
			}
			$selected[$vs] = true;
		}

		$numberRange = new NumberRange(false,
			...array_map(
				fn(Number $number): NumberInterval => NumberInterval::singleNumber($number),
				$subsetValues
			)
		);
		$this->underlyingType = new RealType(
			$numberRange
		);
	}

	public function hydrate(HydrationRequest $request): HydrationSuccess|HydrationFailure {
		return $this->underlyingType->hydrate($request);
	}

	public function isSubtypeOf(Type $ofType): bool {
		return $this->underlyingType->isSubtypeOf($ofType) ||
			($ofType instanceof SupertypeChecker && $ofType->isSupertypeOf($this));
	}

	public function contains(int|float|Number $value): bool {
		if (is_int($value)) {
			$value = new Number($value);
		} elseif (is_float($value)) {
			$value = new Number((string)$value);
		}
		return in_array(
			$value,
			$this->subsetValues
		);
	}

	public function __toString(): string {
		return sprintf("Real[%s]", implode(', ', $this->subsetValues));
	}

	public function validate(ValidationRequest $request): ValidationResult {
		return $request->ok();
	}

	public function jsonSerialize(): array {
		return [
			'type' => 'RealSubset',
			'values' => array_map(fn(Number $value): float => (float)(string)$value, $this->subsetValues)
		];
	}

	public NumberRangeInterface $numberRange {
		get {
			return $this->underlyingType->numberRange;
		}
	}
}
