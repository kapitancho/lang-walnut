<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Type\P;

use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Type\IntersectionType as IntersectionTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Implementation\Type\IntersectionTypeNormalizer;
use Walnut\Lang\Almond\Engine\Implementation\Type\UnionIntersectionHelper;

final readonly class IntersectionType implements SupertypeChecker, IntersectionTypeInterface {
	use UnionIntersectionHelper;

	/** @param non-empty-list<Type> $types */
	public function __construct(
		private IntersectionTypeNormalizer $normalizer,
		public array $types
	) {}

	public function hydrate(HydrationRequest $request): HydrationSuccess|HydrationFailure {
		return $request->withError(
			"Intersection type values cannot be hydrated",
			$this
		);
	}

	public function isSubtypeOf(Type $ofType): bool {
		if (array_any($this->types, fn($type) => $type->isSubtypeOf($ofType))) {
			return true;
		}
		if ($this->isRecordIntersection($ofType)) {
			return true;
		}
		return $ofType instanceof SupertypeChecker && $ofType->isSupertypeOf($this);
	}

	public function isSupertypeOf(Type $ofType): bool {
		return array_any($this->types, fn($type) => $ofType->isSubtypeOf($type));
	}

	public function __toString(): string {
		return sprintf("(%s)", implode('&', $this->types));
	}

	public function validate(ValidationRequest $request): ValidationResult {
		$result = $request->ok();
		foreach ($this->types as $type) {
			$result = $type->validate($result);
		}
		return $result;
	}

	public function jsonSerialize(): array {
		return ['type' => 'Intersection', 'types' => $this->types];
	}

	private function isRecordIntersection(Type $ofType): bool {
		return $this->isRecordHelper($ofType);
	}

}