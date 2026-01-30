<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Type\P;


use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Type\BooleanType as BooleanTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\FalseType as FalseTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Blueprint\Value\BooleanValue as BooleanValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Value\EnumerationValue;
use Walnut\Lang\Almond\Engine\Implementation\Value\P\BooleanValue;

final readonly class FalseType implements FalseTypeInterface, JsonSerializable {
	/** @var array<string, EnumerationValue&BooleanValueInterface> $subsetValues */
	public array $subsetValues;

	public BooleanValueInterface $value;

	public function __construct(
		public BooleanType $enumeration,
	) {
		$this->value = new BooleanValue(
			$this,
			new EnumerationValueName('false'),
			false
		);
		$this->subsetValues = [$this->value];
	}

	public function hydrate(HydrationRequest $request): HydrationSuccess|HydrationFailure {
		if ($request->value instanceof BooleanValueInterface) {
			if (!$request->value->literalValue) {
				return $request->ok($request->value);
			}
			return $request->withError(
				"The boolean value should be 'false'",
				$this
			);
		}
		return $request->withError(
			"The value should be 'false'",
			$this
		);
	}

    public function isSubtypeOf(Type $ofType): bool {
        return match(true) {
            $ofType instanceof FalseTypeInterface, $ofType instanceof BooleanTypeInterface => true,
            $ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
            default => false
        };
    }

	public function __toString(): string {
		return 'False';
	}

	public function validate(ValidationRequest $request): ValidationResult {
		return $request->ok();
	}

	public function jsonSerialize(): array {
		return ['type' => 'False'];
	}
}