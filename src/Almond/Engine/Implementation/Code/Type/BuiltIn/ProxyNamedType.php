<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AliasType as AliasTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;

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