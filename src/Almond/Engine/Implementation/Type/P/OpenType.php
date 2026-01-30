<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Type\P;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Function\UserlandFunction;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Type\OpenType as OpenTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Blueprint\Value\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\VariableScope\VariableScopeFactory;

final readonly class OpenType implements OpenTypeInterface, JsonSerializable {

    public function __construct(
	    private VariableScopeFactory $variableScopeFactory,

	    public TypeName $name,
        public Type $valueType,
	    public UserlandFunction|null $validator
    ) {}

	public function hydrate(HydrationRequest $request): HydrationSuccess|HydrationFailure {
		if ($named = $request->namedTypeHydrator->tryHydrateByName($this, $request)) {
			return $named;
		}
		$valueResult = $this->valueType->hydrate($request);
		if ($valueResult instanceof HydrationFailure) {
			return $valueResult;
		}
		if ($this->validator !== null) {
			$validatorValue = $this->validator->execute(
				$this->variableScopeFactory->emptyVariableValueScope,
				null,
				$valueResult->hydratedValue,
			);
			if ($validatorValue instanceof ErrorValue) {
				return $request->withError(
					sprintf(
						"Could not hydrate value of open type %s: error in validation: %s",
						$this->name,
						$validatorValue
					),
					$this
				);
			}
		}
		return $request->ok(
			$request->valueRegistry->open(
				$this->name,
				$valueResult->hydratedValue
			)
		);
	}

	public function isSubtypeOf(Type $ofType): bool {
		return match(true) {
			$ofType instanceof OpenTypeInterface => $this->name->equals($ofType->name),
			$ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
			default => false
		};
    }

	public function __toString(): string {
		return (string)$this->name;
	}

	public function validate(ValidationRequest $request): ValidationResult {
		return $this->valueType->validate($request);
	}

	public function jsonSerialize(): array {
		return ['type' => 'Open', 'name' => $this->name, 'valueType' => $this->valueType];
	}
}