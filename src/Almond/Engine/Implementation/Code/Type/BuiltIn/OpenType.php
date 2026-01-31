<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\UserlandFunction;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OpenType as OpenTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope\VariableScopeFactory;

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