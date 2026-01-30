<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Type\P;

use Walnut\Lang\Almond\Engine\Blueprint\Error\UnknownProperty as UnknownPropertyInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Type\AnyType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\MapType as MapTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\OptionalKeyType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\RecordType as RecordTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationResult;
use Walnut\Lang\Almond\Engine\Blueprint\Value\RecordValue;
use Walnut\Lang\Almond\Engine\Implementation\Error\UnknownProperty;

final readonly class RecordType implements RecordTypeInterface {

	/**  @param array<string, Type> $types */
	public function __construct(
		private TypeRegistry $typeRegistry,

		public array         $types,
		public Type          $restType
	) {}

	public function hydrate(HydrationRequest $request): HydrationSuccess|HydrationFailure {
		$value = $request->value;
		if ($value instanceof RecordValue) {
			$usedKeys = [];
			foreach($this->types as $key => $refType) {
				$itemRequest = $request->withAddedPathSegment(".$key");

				$isOptional = $refType instanceof OptionalKeyType;
				$item = $value->valueOf($key);
				if ($isOptional && $item instanceof UnknownPropertyInterface) {
					continue;
				}
				$itemResult = $item instanceof UnknownPropertyInterface ?
					$itemRequest->withError(
						sprintf("The record value should contain the key %d", $key),
						$refType
					) :
					$refType->hydrate($itemRequest->forValue($item));
				if ($itemResult instanceof HydrationFailure) {
					$failure = ($failure ?? $request->withError(
						"One or more items in the record failed to hydrate",
						$this,
					))->mergeFailure($itemResult);
				} else {
					$result[$key] = $itemResult->hydratedValue;
				}
			}
			foreach($value->values as $key => $val) {
				if (!isset($usedKeys[$key])) {
					if ($this->restType instanceof NothingType) {
						$failure = $failure ?? $request->withError(
							sprintf("The record value may not contain the key %s", $key),
							$this,
						);
						continue;
					}
					$itemRequest = $request->withAddedPathSegment(".$key");
					$itemResult = $this->restType->hydrate(
						$itemRequest->forValue($val)
					);
					$result[$key] = $itemResult;
					if ($itemResult instanceof HydrationFailure) {
						$failure = ($failure ?? $request->withError(
							"One or more items in the record failed to hydrate",
							$this,
						))->mergeFailure($itemResult);
					}
				}
			}
			return $failure ?? $request->ok($request->valueRegistry->record($result));
		}
		return $request->withError(
			sprintf("The value should be a record with %d items",
				count($this->types),
			),
			$this
		);
	}

	public function typeOf(string $key): Type|UnknownPropertyInterface {
		return $this->types[$key] ?? new UnknownProperty($key, $this);
	}


	public function asMapType(): MapType {
		$l = count($this->types);
		$min = count(array_filter($this->types, static fn($type) => !($type instanceof OptionalKeyType)));
		$types = array_map(
			static fn(Type $type): Type => $type instanceof OptionalKeyType ? $type->valueType : $type,
			$this->types
		);
		return $this->typeRegistry->map(
			$this->typeRegistry->union(array_values([... $types, $this->restType])),
			$min,
			$this->restType instanceof NothingType ? $l : PlusInfinity::value,
			match(true) {
				count($this->types) === 0 => $this->typeRegistry->nothing,
				$this->restType instanceof NothingType => $this->typeRegistry->stringSubset(
					array_map(strval(...), array_keys($this->types))
				),
				default => $this->typeRegistry->string()
			}
		);
	}

	public function isSubtypeOf(Type $ofType): bool {
		return match(true) {
			$ofType instanceof RecordTypeInterface => $this->isSubtypeOfRecord($ofType),
			$ofType instanceof MapTypeInterface => $this->isSubtypeOfMap($ofType),
			$ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
			default => false
		};
	}

	private function isSubtypeOfRecord(RecordTypeInterface $ofType): bool {
		if (!$this->restType->isSubtypeOf($ofType->restType)) {
			return false;
		}
		$ofTypes = $ofType->types;
		$usedKeys = [];
		foreach($this->types as $key => $type) {
			if (!$type->isSubtypeOf($ofTypes[$key] ?? $ofType->restType)) {
				return false;
			}
			$usedKeys[$key] = true;
		}
		return array_all($ofTypes, fn($type, $key) =>
			$type instanceof OptionalKeyType ||
			isset($usedKeys[$key]) ||
			(isset($this->types[$key]) && !$this->types[$key]->isSubtypeOf($type))
		);
	}

	private function isSubtypeOfMap(MapTypeInterface $ofType): bool {
		$keyType = $ofType->keyType;
		$itemType = $ofType->itemType;
		if (!$this->restType->isSubtypeOf($itemType)) {
			return false;
		}
		if (count($this->types) && !$this->typeRegistry->stringSubset(
			array_map(strval(...), array_keys($this->types))
		)->isSubtypeOf($keyType)) {
			return false;
		}
		foreach($this->types as $type) {
			$t = $type instanceof OptionalKeyType ? $type->valueType : $type;
			if (!$t->isSubtypeOf($itemType)) {
				return false;
			}
		}
		$min = count(array_filter($this->types, static fn($type) => !($type instanceof OptionalKeyType)));
		$cnt = count($this->types);
		if ($min < $ofType->range->minLength) {
			return false;
		}
		$max = $ofType->range->maxLength;
		return $max === PlusInfinity::value || ($this->restType instanceof NothingType && $cnt <= $max);
	}

	public function asString(bool $multiline): string {
		$types = [];
		$typeX = '';
		if (count($this->types)) {
			foreach($this->types as $key => $type) {
				$typeStr = (string)$type;
				$typeStr = lcfirst($typeStr) === $key ? "~$typeStr" : "$key: $typeStr";
				$typeStr = $multiline ? "\t" . str_replace("\n", "\n" . "\t", $typeStr) : $typeStr;
				$types[] = $typeStr;
			}
		} else {
			$typeX = ':';
		}
		if ($this->restType instanceof AnyType) {
			$types[] = "...";
			if ($typeX === ':') {
				$typeX = ': ';
			}
		} elseif (!$this->restType instanceof NothingType) {
			$types[] = "... " . $this->restType;
			if ($typeX === ':') {
				$typeX = ': ';
			}
		}
		return $multiline ?
			sprintf("[\n%s%s\n]", $typeX, implode("," . "\n", $types)) :
			sprintf("[%s%s]", $typeX, implode(", ", $types));
	}

	public function __toString(): string {
		$result = $this->asString(false);
		return mb_strlen($result) > 40 ? $this->asString(true) : $result;
	}

	public function validate(ValidationRequest $request): ValidationResult {
		$result = $request->ok();
		foreach ($this->types as $type) {
			$result = $type->validate($result);
		}
		return $this->restType->validate($result);
	}

	public function jsonSerialize(): array {
		return ['type' => 'Record', 'types' => $this->types, 'restType' => $this->restType];
	}

}