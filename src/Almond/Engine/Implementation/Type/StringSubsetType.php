<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Type;

use BcMath\Number;
use InvalidArgumentException;
use JsonSerializable;
use Walnut\Lang\Almond\AST\Blueprint\Parser\EscapeCharHandler;
use Walnut\Lang\Almond\Engine\Blueprint\Error\InvalidArgument;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Range\LengthRange as LengthRangeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\StringSubsetType as StringSubsetTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Blueprint\Type\StringType as StringTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Value\StringValue;
use Walnut\Lang\Almond\Engine\Implementation\Range\LengthRange;

final class StringSubsetType implements StringSubsetTypeInterface, JsonSerializable {

	/** @param list<string> $subsetValues */
	public function __construct(
		private readonly StringTypeInterface  $stringType,
		private readonly EscapeCharHandler    $escapeCharHandler,
		public readonly array                 $subsetValues
	) {
		if ($subsetValues === []) {
			InvalidArgument::of(
				'StringSubset[]',
				$this->subsetValues,
				"Cannot create an empty string subset type"
			);
		}
		$selected = [];
		foreach($subsetValues as $value) {
			if (!is_string($value)) {
				InvalidArgument::of(
					'String',
					$value,
					sprintf("Invalid value: '%s'", $value)
				);
			}
			if (isset($selected[$value])) {
				InvalidArgument::of(
					'StringSubset[]',
					$this->subsetValues,
					sprintf("Duplicate subset value: '%s'", $value)
				);
			}
			$selected[$value] = true;
		}
	}

	public function hydrate(HydrationRequest $request): HydrationSuccess|HydrationFailure {
		$value = $request->value;
		if ($value instanceof StringValue) {
			if ($this->contains($value->literalValue)) {
				return $request->ok($value);
			}
			return $request->withError(
				sprintf("The string value should be among %s",
					implode(', ', $this->subsetValues)
				),
				$this
			);
		}
		return $request->withError(
			sprintf("The value should be a string among %s",
				implode(', ', $this->subsetValues)
			),
			$this
		);
	}

	public function isSubtypeOf(Type $ofType): bool {
		return match(true) {
			$ofType instanceof StringSubsetTypeInterface =>
				self::isSubset($this->subsetValues, $ofType->subsetValues),
			$ofType instanceof StringTypeInterface =>
				self::isInRange($this->subsetValues, $ofType->range),
			count($this->subsetValues) > 1 && $ofType->isSubtypeOf($this->stringType) => array_all(
				$this->subsetValues,
				fn(string $value) => new self(
					$this->stringType,
					$this->escapeCharHandler,
					[$value]
				)->isSubtypeOf($ofType)
			),
			$ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
			default => false
		};
	}

	/** @param list<string> $subsetValues */
	private static function isInRange(array $subsetValues, LengthRangeInterface $range): bool {
		return array_all($subsetValues, fn($value) => $range->lengthInRange(
			new Number(mb_strlen($value))
		));
	}

	private static function isSubset(array $subset, array $superset): bool {
		return array_all($subset, fn($value) => in_array($value, $superset));
	}

	public function contains(string $value): bool {
		return in_array($value, $this->subsetValues, true);
	}

	private function minLength(): Number {
		return new Number(min(array_map(
			static fn(string $value): int =>
				mb_strlen($value), $this->subsetValues
		)));
	}

	private function maxLength(): Number {
		return new Number(max(array_map(
			static fn(string $value): int =>
				mb_strlen($value), $this->subsetValues
		)));
	}

	public LengthRange $range {
		get => $this->range ??= new LengthRange(
			$this->minLength(), $this->maxLength()
		);
	}

	public function validate(ValidationRequest $request): ValidationResult {
		return $request->ok();
	}

	public function jsonSerialize(): array {
		return [
			'type' => 'StringSubset',
			'values' => $this->subsetValues
		];
	}

	public function __toString(): string {
		return sprintf("String[%s]", implode(', ', array_map(
			fn(string $value): string => $this->escapeCharHandler->escape($value),
			$this->subsetValues
		)));
	}
}
