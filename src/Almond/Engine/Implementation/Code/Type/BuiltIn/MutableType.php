<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MutableType as MutableTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;

final readonly class MutableType implements MutableTypeInterface, JsonSerializable {

    public function __construct(
        public Type $valueType
    ) {}

	public function hydrate(HydrationRequest $request): HydrationSuccess|HydrationFailure {
		$result = $this->valueType->hydrate($request);
		if ($result instanceof HydrationSuccess) {
			return $request->ok(
				$request->valueRegistry->mutable(
					$this->valueType,
					$request->value
				)
			);
		}
		return $result;
	}

    public function isSubtypeOf(Type $ofType): bool {
		return match(true) {
			$ofType instanceof MutableTypeInterface =>
				$this->valueType->isSubtypeOf($ofType->valueType) &&
	            $ofType->valueType->isSubtypeOf($this->valueType),
			//TODO: $ofType instanceof AliasType && $ofType->name->equals(CoreType::JsonValue->typeName()) =>
				//$this->valueType->isSubtypeOf($ofType),
			$ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
			default => false
		};
    }

	public function __toString(): string {
		return sprintf("Mutable<%s>", $this->valueType);
	}

	public function validate(ValidationRequest $request): ValidationResult {
		return $this->valueType->validate($request);
	}

	public function jsonSerialize(): array {
		return ['type' => 'Mutable', 'valueType' => $this->valueType];
	}
}