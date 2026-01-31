<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AliasType as AliasTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;

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