<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;
use Walnut\Lang\Blueprint\Type\AliasType;
use Walnut\Lang\Blueprint\Type\AtomType;
use Walnut\Lang\Blueprint\Type\DataType;
use Walnut\Lang\Blueprint\Type\EnumerationSubsetType;
use Walnut\Lang\Blueprint\Type\EnumerationType;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\IntersectionType;
use Walnut\Lang\Blueprint\Type\MetaType as MetaTypeInterface;
use Walnut\Lang\Blueprint\Type\MutableType;
use Walnut\Lang\Blueprint\Type\NamedType;
use Walnut\Lang\Blueprint\Type\OpenType;
use Walnut\Lang\Blueprint\Type\RealSubsetType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\SupertypeChecker;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\UnionType;

final readonly class MetaType implements MetaTypeInterface, SupertypeChecker, JsonSerializable {
	public function __construct(
		public MetaTypeValue $value
	) {}

	public function __toString(): string {
		return $this->value->value;
	}

	public function isSupertypeOf(Type $ofType): bool {
		if ($ofType instanceof self) {
			if ($this->value === $ofType->value) {
				return true;
			}
			$isSupertype = match($this->value) {
				MetaTypeValue::Named => in_array($ofType->value, [
					MetaTypeValue::Atom, MetaTypeValue::Enumeration, MetaTypeValue::Data,
					MetaTypeValue::Open, MetaTypeValue::Sealed
				], true),
				MetaTypeValue::Enumeration => $ofType->value === MetaTypeValue::EnumerationSubset,
				default => false
			};
			if ($isSupertype) {
				return true;
			}
		}
		$result = match($this->value) {
			MetaTypeValue::Function => $ofType instanceof FunctionType,
			MetaTypeValue::Tuple => $ofType instanceof TupleType,
			MetaTypeValue::Record => $ofType instanceof RecordType,
			MetaTypeValue::Union => $ofType instanceof UnionType,
			MetaTypeValue::Intersection => $ofType instanceof IntersectionType,
			MetaTypeValue::Alias => $ofType instanceof AliasType,
			MetaTypeValue::Data => $ofType instanceof DataType,
			MetaTypeValue::Open => $ofType instanceof OpenType,
			MetaTypeValue::Sealed => $ofType instanceof SealedType,
			MetaTypeValue::Atom => $ofType instanceof AtomType,
			MetaTypeValue::Enumeration => $ofType instanceof EnumerationType || $ofType instanceof EnumerationSubsetType,
			MetaTypeValue::EnumerationSubset => $ofType instanceof EnumerationSubsetType,
			MetaTypeValue::IntegerSubset => $ofType instanceof IntegerSubsetType,
			MetaTypeValue::MutableValue => $ofType instanceof MutableType,
			MetaTypeValue::RealSubset => $ofType instanceof RealSubsetType,
			MetaTypeValue::StringSubset => $ofType instanceof StringSubsetType,
			MetaTypeValue::Named => $ofType instanceof NamedType,
		};
		return $result || ($ofType instanceof AliasType && $this->isSupertypeOf($ofType->aliasedType));
    }

	public function isSubtypeOf(Type $ofType): bool {
		return $ofType instanceof SupertypeChecker && $ofType->isSupertypeOf($this);
	}

	public function jsonSerialize(): array {
		return [
			'type' => 'MetaType',
			'metaType' => $this->value->value
		];
	}
}