<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\EnumerationSubsetType as EnumerationSubsetTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\EnumerationType as EnumerationTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\DuplicateSubsetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\InvalidArgument;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\EnumerationValue;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;

final readonly class EnumerationSubsetType implements EnumerationSubsetTypeInterface, JsonSerializable {

	/** @var array<string, EnumerationValue> */
	public array $subsetValues;

    /** @param array<string, EnumerationValue> $subsetValues */
    public function __construct(
        public EnumerationTypeInterface $enumeration,
        array $subsetValues
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
		$this->subsetValues = $subsetValuesMap;
    }

	public function hydrate(HydrationRequest $request): HydrationSuccess|HydrationFailure {
		$result = $this->enumeration->hydrate($request);
		if ($result instanceof HydrationFailure) {
			return $result;
		}
		if (!in_array($result->hydratedValue, $this->subsetValues)) {
			return $request->withError(
				sprintf(
					"Value %s is not part of the subset %s.",
					$result->hydratedValue,
					$this
				),
				$this,
			);
		}
		return $result;
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