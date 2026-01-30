<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Type\P;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Type\AliasType as AliasTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;

final readonly class AliasType implements AliasTypeInterface, SupertypeChecker, JsonSerializable {

    public function __construct(
	    public TypeName $name,
        public Type $aliasedType
    ) {}

	public function hydrate(HydrationRequest $request): HydrationSuccess|HydrationFailure {
		return $request->namedTypeHydrator->tryHydrateByName($this, $request) ??
			$this->aliasedType->hydrate($request);
	}

	public function isSubtypeOf(Type $ofType): bool {
		if ($ofType instanceof AliasTypeInterface && $this->name->equals($ofType->name)) {
			return true;
		}
		return (
			$this->aliasedType->isSubtypeOf($ofType)
			) || (
				$ofType instanceof SupertypeChecker &&
				$ofType->isSupertypeOf($this)
			);
	}

	public function __toString(): string {
		return (string)$this->name;
	}

	public function isSupertypeOf(Type $ofType): bool {
		return $ofType->isSubtypeOf($this->aliasedType);
	}

	public function validate(ValidationRequest $request): ValidationResult {
		return $this->aliasedType->validate($request);
	}

	public function jsonSerialize(): array {
		return [
			'type' => 'Alias',
			'name' => $this->name,
			'aliasedType' => $this->aliasedType
		];
	}
}