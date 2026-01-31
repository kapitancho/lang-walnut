<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn;

use BcMath\Number;
use InvalidArgumentException;
use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerSubsetType as IntegerSubsetTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType as IntegerTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\DuplicateSubsetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\InvalidArgument;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberInterval;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberRange;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;

final class IntegerSubsetType implements IntegerSubsetTypeInterface, JsonSerializable {

	private readonly IntegerTypeInterface $underlyingType;

	/**
	 * @param list<Number> $subsetValues
	 * @throws InvalidArgumentException
	 */
	public function __construct(
		public readonly array $subsetValues
	) {
		if ($subsetValues === []) {
			InvalidArgument::of(
				'IntegerSubset[]',
				$this->subsetValues,
				"Cannot create an empty integer subset type"
			);
		}
		$selected = [];
		foreach($subsetValues as $value) {
			if (!$value instanceof Number || ((string)$value !== (string)$value->floor())) {
				InvalidArgument::of(
					'String',
					$value,
					sprintf("Invalid value: '%s'", $value)
				);
			}
			$vs = (string)$value;
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
				fn(Number $number): NumberInterval =>
					NumberInterval::singleNumber($number),
				$subsetValues
			)
		);
		$this->underlyingType = new IntegerType(
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

	public function contains(int|Number $value): bool {
		if (is_int($value)) {
			$value = new Number($value);
		}
		return in_array($value, $this->subsetValues);
	}

	public function __toString(): string {
		return sprintf("Integer[%s]", implode(', ', $this->subsetValues));
	}

	public function validate(ValidationRequest $request): ValidationResult {
		return $request->ok();
	}

	public function jsonSerialize(): array {
		return [
			'type' => 'IntegerSubset',
			'values' => array_map(fn(Number $value): int => (int)(string)$value, $this->subsetValues)
		];
	}

	public NumberRange $numberRange {
		get {
			return $this->underlyingType->numberRange;
		}
	}
}
