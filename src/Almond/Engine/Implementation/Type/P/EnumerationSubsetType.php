<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Type\P;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Error\DuplicateSubsetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Error\InvalidArgument;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Type\EnumerationSubsetType as EnumerationSubsetTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\EnumerationType as EnumerationTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Blueprint\Value\EnumerationValue;

final readonly class EnumerationSubsetType implements EnumerationSubsetTypeInterface, JsonSerializable {

    /** @param array<string, EnumerationValue> $subsetValues */
    public function __construct(
        public EnumerationTypeInterface $enumeration,
        public array                    $subsetValues
    ) {
	    if ($subsetValues === []) {
		    InvalidArgument::of(
			    'EnumerationValueName[]',
			    $subsetValues,
			    "Cannot create an empty subset type"
		    );
	    }
	    $subsetValuesMap = [];
	    foreach($subsetValues as $value) {
		    if (array_key_exists($value->name->identifier, $subsetValuesMap)) {
			    DuplicateSubsetValue::of($this, $value);
		    }
		    // @codeCoverageIgnoreStart
		    if (!$value instanceof EnumerationValue) {
			    InvalidArgument::of(
				    'EnumerationValue',
				    $value
			    );
		    }
		    // @codeCoverageIgnoreEnd
		    $subsetValuesMap[$value->name->identifier] = $value;
	    }
    }

	public function hydrate(HydrationRequest $request): HydrationSuccess|HydrationFailure {
		return $request->ok($request->value);
	}

	public function isSubtypeOf(Type $ofType): bool {
        return match(true) {
            $ofType instanceof EnumerationTypeInterface =>
                $this->enumeration->name->identifier === $ofType->name->identifier,
            $ofType instanceof EnumerationSubsetTypeInterface =>
            	$this->enumeration->name->equals($ofType->enumeration->name) &&
                self::isSubset($this->subsetValues, $ofType->subsetValues),
            $ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
            default => false
        };
    }

    private static function isSubset(array $subset, array $superset): bool {
	    return array_all($subset, fn(EnumerationValue $value, string $key) => isset($superset[$key]));
    }

	public function __toString(): string {
		return sprintf("%s[%s]",
			$this->enumeration,
			implode(', ', array_map(
				static fn(EnumerationValue $value) => $value->name,
				$this->subsetValues
			))
		);
	}

	public function validate(ValidationRequest $request): ValidationResult {
		return $request->ok();
	}

	public function jsonSerialize(): array {
		return [
			'type' => 'EnumerationSubsetType',
			'enumerationName' => $this->enumeration->name,
			'subsetValues' => $this->subsetValues
		];
	}
}