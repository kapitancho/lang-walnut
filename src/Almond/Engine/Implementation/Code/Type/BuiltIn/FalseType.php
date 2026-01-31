<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn;


use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BooleanType as BooleanTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FalseType as FalseTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue as BooleanValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\EnumerationValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Implementation\Code\Value\BuiltIn\BooleanValue;

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