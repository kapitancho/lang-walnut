<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Type\P;

use Walnut\Lang\Almond\Engine\Blueprint\Error\UnknownProperty as UnknownPropertyInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Type\AnyType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\ArrayType as ArrayTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Type\TupleType as TupleTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Blueprint\Value\TupleValue;
use Walnut\Lang\Almond\Engine\Implementation\Error\UnknownProperty;

final readonly class TupleType implements TupleTypeInterface {

	/**  @param list<Type> $types */
	public function __construct(
		private TypeRegistry $typeRegistry,

		public array         $types,
		public Type          $restType
	) {}

	public function asArrayType(): ArrayTypeInterface {
		$l = count($this->types);
		return $this->typeRegistry->array(
			$this->typeRegistry->union([
				...
					/** @phpstan-ignore-next-line arrayValues.list */
				array_values($this->types),
				$this->restType
			]),
			$l,
			$this->restType instanceof NothingType ? $l : PlusInfinity::value,
		);
	}

	public function hydrate(HydrationRequest $request): HydrationSuccess|HydrationFailure {
		$value = $request->value;
		if ($value instanceof TupleValue) {
			$l = count($this->types);
			if ($this->restType instanceof NothingType && count($value->values) > $l) {
				return $request->withError(
					sprintf("The tuple value should be with %d items", $l),
					$this
				);
			}
			$result = [];
			$failure = null;
			foreach($this->types as $seq => $refType) {
				$item = $value->valueOf($seq);
				$itemResult = $item instanceof UnknownPropertyInterface ?
					$request->withAddedPathSegment("[$seq]")
						->withError(
							sprintf("The tuple value should contain the index %d", $seq),
							$refType
						) :
					$refType->hydrate(
						$request->forValue($item)->withAddedPathSegment("[$seq]")
					);
				// This is intentional so that the rest items can be hydrated even if some of the fixed items failed
				if ($itemResult instanceof HydrationFailure) {
					$result[$seq] = null;
					$failure = ($failure ?? $request->withError(
						"One or more items in the tuple failed to hydrate",
						$this,
					))->mergeFailure($itemResult);
				} else {
					$result[$seq] = $itemResult->hydratedValue;
				}
			}
			foreach($value->values as $seq => $val) {
				if (!isset($result[$seq])) {
					$itemResult = $this->restType->hydrate(
						$request->forValue($val)->withAddedPathSegment("[$seq]")
					);
					if ($itemResult instanceof HydrationFailure) {
						$failure = ($failure ?? $request->withError(
							"One or more items in the tuple failed to hydrate",
							$this,
						))->mergeFailure($itemResult);
					} else {
						$result[] = $itemResult->hydratedValue;
					}
				}
			}
			return $failure ?? $request->ok($request->valueRegistry->tuple($result));
		}
		return $request->withError(
			sprintf("The value should be a tuple with %d items",
				count($this->types),
			),
			$this
		);
	}

	public function typeOf(int $index): Type|UnknownPropertyInterface {
		return $this->types[$index] ?? new UnknownProperty($index, $this);
	}

	public function isSubtypeOf(Type $ofType): bool {
		return match(true) {
			$ofType instanceof TupleTypeInterface => $this->isSubtypeOfTuple($ofType),
			$ofType instanceof ArrayTypeInterface => $this->isSubtypeOfArray($ofType),
			$ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
			default => false
		};
	}

	private function isSubtypeOfTuple(TupleTypeInterface $ofType): bool {
		if (!$this->restType->isSubtypeOf($ofType->restType)) {
			return false;
		}
		$ofTypes = $ofType->types;
		$usedIndices = [];
		foreach($this->types as $index => $type) {
			if (!$type->isSubtypeOf($ofTypes[$index] ?? $ofType->restType)) {
				return false;
			}
			$usedIndices[$index] = true;
		}
		return array_all($ofTypes, fn($type, $index) =>
			isset($usedIndices[$index]) || (
				isset($this->types[$index]) &&
				!$this->types[$index]->isSubtypeOf($type)
			)
		);
	}

	private function isSubtypeOfArray(ArrayTypeInterface $ofType): bool {
		$itemType = $ofType->itemType;
		if (!$this->restType->isSubtypeOf($itemType)) {
			return false;
		}
		if (array_any($this->types, fn($type) => !$type->isSubtypeOf($itemType))) {
			return false;
		}
		$cnt = count($this->types);
		if ($cnt < $ofType->range->minLength) {
			return false;
		}
		$max = $ofType->range->maxLength;
		return $max === PlusInfinity::value || ($this->restType instanceof NothingType && $cnt <= $max);
	}

	public function __toString(): string {
		$types = $this->types;
		if ($this->restType instanceof AnyType) {
			$types[] = "...";
		} elseif (!$this->restType instanceof NothingType) {
			$types[] = "... " . $this->restType;
		}
		return "[" . implode(', ', $types) . "]";
	}

	public function validate(ValidationRequest $request): ValidationResult {
		$result = $request->ok();
		foreach ($this->types as $type) {
			$result = $type->validate($result);
		}
		return $this->restType->validate($result);
	}

	public function jsonSerialize(): array {
		return ['type' => 'Tuple', 'types' => $this->types, 'restType' => $this->restType];
	}

}