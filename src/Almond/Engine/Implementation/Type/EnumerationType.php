<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Error\DuplicateSubsetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Error\InvalidArgument;
use Walnut\Lang\Almond\Engine\Blueprint\Error\UnknownEnumerationValue;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Type\EnumerationSubsetType as EnumerationSubsetTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Blueprint\Type\EnumerationType as EnumerationTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Value\EnumerationValue as EnumerationValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Value\StringValue;
use Walnut\Lang\Almond\Engine\Implementation\Type\P\EnumerationSubsetType;
use Walnut\Lang\Almond\Engine\Implementation\Value\P\EnumerationValue;

final class EnumerationType implements EnumerationTypeInterface, JsonSerializable {

	/** @var array<string, EnumerationValueInterface> */
	public readonly array $values;

    /** @param list<EnumerationValueName> $valueList */
    public function __construct(
        public readonly TypeName $name,
		array $valueList,
    ) {
	    if ($valueList === []) {
		    InvalidArgument::of(
			    $this->name->identifier,
			    $valueList,
			    "Cannot create an empty enumeration type"
		    );
	    }
		$e = [];
	    foreach ($valueList as $value) {
		    // @codeCoverageIgnoreStart
		    /** @phpstan-ignore-next-line identical.alwaysTrue */
		    if (!$value instanceof EnumerationValueName) {
				InvalidArgument::of(
				    'EnumerationValueName',
				    $value
				);
		    }
			if (array_key_exists($value->identifier, $e)) {
				DuplicateSubsetValue::of($this, $value);
			}
		    // @codeCoverageIgnoreEnd
		    $e[$value->identifier] = new EnumerationValue(
				$this,
			    $value
		    );
	    }
		$this->values = $e;
    }

	/** @throws UnknownEnumerationValue **/
	public function value(EnumerationValueName $valueIdentifier): EnumerationValueInterface {
		return $this->values[$valueIdentifier->identifier] ??
			UnknownEnumerationValue::of($this, $valueIdentifier);
	}

	public function hydrate(HydrationRequest $request): HydrationSuccess|HydrationFailure {
		if ($named = $request->namedTypeHydrator->tryHydrateByName($this, $request)) {
			return $named;
		}
		if ($request->value instanceof StringValue) {
			try {
				return $request->ok($this->value(
					new EnumerationValueName($request->value->literalValue)
				));
			} catch (UnknownEnumerationValue $e) {
				return $request->withError(
					sprintf(
						"Enumeration type %s hydration failed. %s",
						$this->name,
						$e->getMessage()
					),
					$this,
				);
			}
		}
		return $request->withError(
			sprintf(
				"Enumeration type %s hydration failed. Expected String, got `%s`.",
				$this->name,
				$request->value
			),
			$this,
		);
	}

    public function isSubtypeOf(Type $ofType): bool {
		return match (true) {
			$ofType instanceof EnumerationTypeInterface => $this->name->equals($ofType->name),
			$ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
			default => false
		};
	}

	/**
	 * @param non-empty-list<EnumerationValueName> $values
	 * @throws UnknownEnumerationValue|DuplicateSubsetValue|InvalidArgument
	 **/
    public function subsetType(array $values): EnumerationSubsetTypeInterface {
		return new EnumerationSubsetType(
			$this,
			array_map(
				fn($valueName): EnumerationValueInterface =>
					$valueName instanceof EnumerationValueName ?
						$this->value($valueName) :
						InvalidArgument::of(
							'EnumerationValueName',
							$valueName
						),
				$values
			)
		);
	}

	public EnumerationType $enumeration {
		get {
			return $this;
		}
	}

	/** @param array<string, EnumerationValueInterface> $subsetValues */
	public array $subsetValues {
		get {
			return $this->values;
		}
	}

	public function __toString(): string {
		return (string)$this->name;
	}

	public function validate(ValidationRequest $request): ValidationResult {
		return $request->ok();
	}

	public function jsonSerialize(): array {
		return ['type' => 'Enumeration', 'name' => (string)$this->name, 'values' => $this->values];
	}
}