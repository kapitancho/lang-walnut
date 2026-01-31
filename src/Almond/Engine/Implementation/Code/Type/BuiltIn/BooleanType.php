<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BooleanType as BooleanTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FalseType as FalseTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TrueType as TrueTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\DuplicateSubsetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\InvalidArgument;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\UnknownEnumerationValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue as BooleanValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;

final class BooleanType implements BooleanTypeInterface, JsonSerializable {

	/** @var array<string, BooleanValueInterface> $enumerationValues */
	public readonly array $values;
	public readonly TrueTypeInterface $trueType;
	public readonly BooleanValueInterface $trueValue;
	public readonly FalseTypeInterface $falseType;
	public readonly BooleanValueInterface $falseValue;

    public function __construct(
		public readonly TypeName $name
	) {
	    $this->trueType = new TrueType($this);
	    $this->falseType = new FalseType($this);
		$this->trueValue = $this->trueType->value;
		$this->falseValue = $this->falseType->value;
	    $this->values = [
		    'true' => $this->trueValue,
		    'false' => $this->falseValue
	    ];
    }

	public function hydrate(HydrationRequest $request): HydrationSuccess|HydrationFailure {
		if ($request->value instanceof BooleanValueInterface) {
			return $request->ok($request->value);
		}
		return $request->withError(
			"The value should be a boolean",
			$this
		);
	}


	/**
	 * @param non-empty-list<EnumerationValueName> $values
	 * @throws UnknownEnumerationValue|DuplicateSubsetValue|InvalidArgument
	 **/
	public function subsetType(array $values): TrueTypeInterface|FalseTypeInterface|BooleanTypeInterface {
		/** @phpstan-ignore-next-line identical.alwaysFalse */
		if ($values === []) {
			InvalidArgument::of(
				'EnumerationValueName[]',
				$values,
				"Cannot create an empty subset type"
			);
		}
		$v = $values[0];
		if (count($values) === 1) {
			if ($v->identifier === $this->trueType->value->name->identifier) {
				return $this->trueType;
			}
			if ($v->identifier === $this->falseType->value->name->identifier) {
				return $this->falseType;
			}
		}
		foreach($values as $value) {
			$v = $this->values[$value->identifier] ?? null;
			if ($v === null) {
				UnknownEnumerationValue::of($this, $value);
			}
		}
		return $this;
	}

	/** @throws UnknownEnumerationValue **/
	public function value(EnumerationValueName $valueIdentifier): BooleanValueInterface {
		return $this->values[$valueIdentifier->identifier] ??
			UnknownEnumerationValue::of($this, $valueIdentifier);
	}

	public BooleanTypeInterface $enumeration {
		get {
			return $this;
		}
	}

	/** @param list<BooleanValueInterface> $subsetValues */
	public array $subsetValues {
		get {
			return $this->values;
		}
	}

	public function isSubtypeOf(Type $ofType): bool {
        return $ofType instanceof BooleanTypeInterface || (
            $ofType instanceof SupertypeChecker &&
            $ofType->isSupertypeOf($this)
        );
    }

	public function __toString(): string {
		return 'Boolean';
	}

	public function validate(ValidationRequest $request): ValidationResult {
		return $request->ok();
	}

	public function jsonSerialize(): array {
		return ['type' => 'Boolean'];
	}
}