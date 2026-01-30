<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Type\P;

use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Type\AliasType as AliasTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;

final class ProxyNamedType implements SupertypeChecker, AliasTypeInterface {

	public function __construct(
		private readonly TypeRegistry $typeRegistry,

		public readonly TypeName $name
	) {}

	public Type $aliasedType { get => $this->typeRegistry->typeByName($this->name); }

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

	public function isSupertypeOf(Type $ofType): bool {
		return $ofType->isSubtypeOf($this->aliasedType);
	}

	public function __toString(): string {
		return (string)$this->name;
	}

	// This should stop in order to avoid infinite recursion
	public function validate(ValidationRequest $request): ValidationResult {
		return $request->ok();
	}

	public function jsonSerialize(): array {
		return [
			'type' => 'Proxy',
			'name' => (string)$this->name
		];
	}
}