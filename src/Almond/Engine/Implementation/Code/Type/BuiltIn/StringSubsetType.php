<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn;

use BcMath\Number;
use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringSubsetType as StringSubsetTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType as StringTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\InvalidArgument;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\EscapeCharHandler;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\LengthRange;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;

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
	private static function isInRange(array $subsetValues, LengthRange $range): bool {
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
