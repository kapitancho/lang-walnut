<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Type\P;

use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Type\UnionType as UnionTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Implementation\Type\UnionIntersectionHelper;
use Walnut\Lang\Almond\Engine\Implementation\Type\UnionTypeNormalizer;

final readonly class UnionType implements SupertypeChecker, UnionTypeInterface {
	use UnionIntersectionHelper;

	/** @param non-empty-list<Type> $types */
	public function __construct(
		private UnionTypeNormalizer $normalizer,
		public array $types
	) {}

	public function hydrate(HydrationRequest $request): HydrationSuccess|HydrationFailure {
		$failures = [];
		foreach($this->types as $type) {
			$result = $type->hydrate($request);
			if ($result instanceof HydrationSuccess) {
				return $result;
			}
			$failures[] = $result;
		}
		$result = $request->withError(
			"Value does not match any type in the union",
			$this,
		);
		foreach ($failures as $failure) {
			$result = $result->mergeFailure($failure);
		}
		return $result;
	}

	public function isSubtypeOf(Type $ofType): bool {
		if (array_any($this->types, fn($type) => !$type->isSubtypeOf($ofType))) {
			return ($ofType instanceof SupertypeChecker && $ofType->isSupertypeOf($this)) ||
				$this->isRecordUnion($ofType);
		}
		return true;
	}

	public function isSupertypeOf(Type $ofType): bool {
		return array_any($this->types, fn($type) => $ofType->isSubtypeOf($type));
	}

	public function __toString(): string {
		return sprintf("(%s)", implode('|', $this->types));
	}

	public function validate(ValidationRequest $request): ValidationResult {
		$result = $request->ok();
		foreach ($this->types as $type) {
			$result = $type->validate($result);
		}
		return $result;
	}

	public function jsonSerialize(): array {
		return ['type' => 'Union', 'types' => $this->types];
	}

	private function isRecordUnion(Type $ofType): bool {
		return $this->isRecordHelper($ofType);
	}

}