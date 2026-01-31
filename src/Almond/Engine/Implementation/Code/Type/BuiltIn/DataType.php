<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\DataType as DataTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;

final readonly class DataType implements DataTypeInterface, JsonSerializable {

    public function __construct(
	    public TypeName $name,
        public Type     $valueType
    ) {}

	public function hydrate(HydrationRequest $request): HydrationSuccess|HydrationFailure {
		if ($named = $request->namedTypeHydrator->tryHydrateByName($this, $request)) {
			return $named;
		}
		$valueResult = $this->valueType->hydrate($request);
		if ($valueResult instanceof HydrationSuccess) {
			return $request->ok(
				$request->valueRegistry->data(
					$this->name,
					$valueResult->hydratedValue
				)
			);
		}
		return $valueResult;
	}

	public function isSubtypeOf(Type $ofType): bool {
		return match(true) {
			$ofType instanceof DataTypeInterface => $this->name->equals($ofType->name),
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
		return ['type' => 'Data', 'name' => $this->name, 'valueType' => $this->valueType];
	}
}