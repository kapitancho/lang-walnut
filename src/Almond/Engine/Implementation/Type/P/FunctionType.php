<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Type\P;

use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Type\FunctionType as FunctionTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;

final readonly class FunctionType implements FunctionTypeInterface {
	public function __construct(
		public Type $parameterType,
		public Type $returnType
	) {}

	public function hydrate(HydrationRequest $request): HydrationSuccess|HydrationFailure {
		return $request->withError(
			"Functions cannot be hydrated",
			$this
		);
	}

	public function isSubtypeOf(Type $ofType): bool {
		return match(true) {
			$ofType instanceof FunctionTypeInterface =>
				$ofType->parameterType->isSubtypeOf($this->parameterType) &&
				$this->returnType->isSubtypeOf($ofType->returnType),
			$ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
			default => false
		};
	}

	public function __toString(): string {
		return sprintf(
			'^%s => %s',
			$this->parameterType,
			$this->returnType,
		);
	}

	public function validate(ValidationRequest $request): ValidationResult {
		return $request
			|> $this->parameterType->validate(...)
			|> $this->returnType->validate(...);
	}

	public function jsonSerialize(): array {
		return [
			'type' => 'Function',
			'parameter' => $this->parameterType,
			'return' => $this->returnType
		];
	}
}