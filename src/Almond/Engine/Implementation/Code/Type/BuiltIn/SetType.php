<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType as SetTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\LengthRange;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;

final readonly class SetType implements SetTypeInterface, JsonSerializable {

    public function __construct(
	    public Type        $itemType,
		public LengthRange $range
    ) {}

	public function hydrate(HydrationRequest $request): HydrationSuccess|HydrationFailure {
		$value = $request->value;
		if ($value instanceof TupleValue || $value instanceof SetValue) {
			$refType = $this->itemType;
			$result = [];
			$failure = null;
			foreach($value->values as $seq => $item) {
				$itemResult = $refType->hydrate(
					$request->forValue($item)->withAddedPathSegment("[$seq]")
				);
				if ($itemResult instanceof HydrationFailure) {
					$failure = ($failure ?? $request->withError(
						"One or more items in the array failed to hydrate",
						$this,
					))->mergeFailure($itemResult);
				} else {
					$result[] = $itemResult->hydratedValue;
				}
			}
			if ($failure) {
				return $failure;
			}
			$set = $request->valueRegistry->set($result);
			$l = count($set->values);
			if ($this->range->minLength <= $l && (
				$this->range->maxLength === PlusInfinity::value ||
				$this->range->maxLength >= $l
			)) {
				return $request->ok($set);
			}
			return $request->withError(
				sprintf("The set value should be with a length between %s and %s",
					$this->range->minLength,
					$this->range->maxLength === PlusInfinity::value ? "+Infinity" : $this->range->maxLength,
				),
				$this
			);
		}
		return $request->withError(
			sprintf("The value should be a set with a length between %s and %s",
				$this->range->minLength,
				$this->range->maxLength === PlusInfinity::value ? "+Infinity" : $this->range->maxLength,
			),
			$this
		);
	}

	public function isSubtypeOf(Type $ofType): bool {
        return match(true) {
            $ofType instanceof SetTypeInterface =>
                $this->itemType->isSubtypeOf($ofType->itemType) &&
                $this->range->isSubRangeOf($ofType->range),
            $ofType instanceof SupertypeChecker =>
                $ofType->isSupertypeOf($this),
            default => false
        };
    }

	public function __toString(): string {
		$itemType = $this->itemType;

		$range = (string)$this->range;
		if (
			$this->range->maxLength !== PlusInfinity::value &&
			(string)$this->range->minLength === (string)$this->range->maxLength
		) {
			$range = (string)$this->range->minLength;
		}

		$type = "Set<$itemType, $range>";
		return str_replace(["<Any, ..>", "<Any, ", ", ..>"], ["", "<", ">"], $type);
	}

	public function validate(ValidationRequest $request): ValidationResult {
		return $this->itemType->validate($request);
	}

	public function jsonSerialize(): array {
		return [
			'type' => 'Set',
			'itemType' => $this->itemType,
			'range' => $this->range
		];
	}
}