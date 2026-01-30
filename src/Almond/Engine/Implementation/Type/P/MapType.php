<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Type\P;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Range\LengthRange;
use Walnut\Lang\Almond\Engine\Blueprint\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Type\MapType as MapTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Blueprint\Value\RecordValue;

final readonly class MapType implements MapTypeInterface, JsonSerializable {

    public function __construct(
		private StringType $stringType,

		public Type        $keyType,
		public Type        $itemType,
		public LengthRange $range
    ) {}

	public function hydrate(HydrationRequest $request): HydrationSuccess|HydrationFailure {
		$value = $request->value;
		if ($value instanceof RecordValue) {
			$l = count($value->values);
			if ($this->range->minLength <= $l && (
				$this->range->maxLength === PlusInfinity::value ||
				$this->range->maxLength >= $l
			)) {
				$refType = $this->itemType;
				$result = [];
				$failure = null;
				foreach($value->values as $key => $item) {
					$itemResult = $refType->hydrate(
						$request->forValue($item)->withAddedPathSegment(".$key")
					);
					if ($itemResult instanceof HydrationFailure) {
						$failure = ($failure ?? $request->withError(
							"One or more items in the map failed to hydrate",
							$this,
						))->mergeFailure($itemResult);
					} else {
						$result[$key] = $itemResult->hydratedValue;
					}
				}
				return $failure ?? $request->ok($request->valueRegistry->record($result));
			}
			return $request->withError(
				sprintf("The map value should be with a length between %s and %s",
					$this->range->minLength,
					$this->range->maxLength === PlusInfinity::value ? "+Infinity" : $this->range->maxLength,
				),
				$this
			);
		}
		return $request->withError(
			sprintf("The value should be a map with a length between %s and %s",
				$this->range->minLength,
				$this->range->maxLength === PlusInfinity::value ? "+Infinity" : $this->range->maxLength,
			),
			$this
		);
	}

	private function validateKeyType(ValidationRequest $context): ValidationResult {
		if ($this->keyType->isSubtypeOf($this->stringType)) {
			return $context->ok();
		}
		return $context->withError(
			ValidationErrorType::mapKeyTypeMismatch,
			sprintf("Map key type must be a subtype of String, %s given.",
				$this->keyType
			),
			$this
		);
	}

	public function validate(ValidationRequest $request): ValidationResult {
		$result = $this->validateKeyType($request);
		$result = $this->keyType->validate($result);
		return $this->itemType->validate($result);
	}

	public function isSubtypeOf(Type $ofType): bool {
        return match(true) {
            $ofType instanceof MapTypeInterface =>
                $this->itemType->isSubtypeOf($ofType->itemType) &&
                $this->keyType->isSubtypeOf($ofType->keyType) &&
                $this->range->isSubRangeOf($ofType->range),
            $ofType instanceof SupertypeChecker =>
                $ofType->isSupertypeOf($this),
            default => false
        };
    }

	public function __toString(): string {
		$itemType = $this->itemType;
		$keyTypeStr = (string)$this->keyType;
		$keyType = $keyTypeStr === 'String' ? '' : "$keyTypeStr:";

		$range = (string)$this->range;
		if (
			$this->range->maxLength !== PlusInfinity::value &&
			(string)$this->range->minLength === (string)$this->range->maxLength
		) {
			$range = (string)$this->range->minLength;
		}

		$type = "Map<$keyType$itemType, $range>";
		return str_replace(["<Any, ..>", "<Any, ", ", ..>"], ["", "<", ">"], $type);
	}

	public function jsonSerialize(): array {
		return [
			'type' => 'Map',
			'keyType' => $this->keyType,
			'itemType' => $this->itemType,
			'range' => $this->range
		];
	}
}