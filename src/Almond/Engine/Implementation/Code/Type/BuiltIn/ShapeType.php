<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\MethodContext;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ShapeType as ShapeTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class ShapeType implements ShapeTypeInterface, SupertypeChecker, JsonSerializable {
	use BaseType;

    public function __construct(
		private TypeRegistry $typeRegistry,
		private MethodContext $methodContext,

        public Type $refType
    ) {}

	public function hydrate(HydrationRequest $request): HydrationSuccess|HydrationFailure {
		return $this->refType->hydrate($request);
	}

	public function isSubtypeOf(Type $ofType): bool {
		return match(true) {
			$ofType instanceof ShapeTypeInterface => $this->refType->isSubtypeOf($ofType->refType),
			$ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
			default => false,
		};
	}

	private function isShapeOf(Type $ofType): bool {
		$baseType = $this->toBaseType($this->refType);
		if ($baseType instanceof IntersectionType) {
			return array_all($baseType->types, fn($checkType) =>
				$this->typeRegistry->shape($checkType)->isShapeOf($ofType));
		}
		$refTypes = $baseType instanceof UnionType ? $baseType->types : [];
		foreach([$this->refType, ...$refTypes] as $checkType) {
			$canCast = $this->methodContext->validateCast(
				$ofType,
				$checkType,
				null
			);
			if ($canCast instanceof ValidationSuccess && $canCast->type->isSubtypeOf($this->refType)) {
				return true;
			}
		}
		return false;
	}

	public function isSupertypeOf(Type $ofType): bool {
		return
			($ofType instanceof DataType && $ofType->valueType->isSubtypeOf($this)) ||
			$ofType->isSubtypeOf($this->refType) ||
			$this->isShapeOf($ofType);
	}

	public function __toString(): string {
		if ($this->refType instanceof AnyType) {
			return "Shape";
		}
		return sprintf(
			"Shape<%s>",
			$this->refType
		);
	}

	public function validate(ValidationRequest $request): ValidationResult {
		return $this->refType->validate($request);
	}

	public function jsonSerialize(): array {
		return ['type' => 'Shape', 'refType' => $this->refType];
	}

}