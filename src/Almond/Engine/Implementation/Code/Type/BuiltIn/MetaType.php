<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn;

use JsonSerializable;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AliasType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\DataType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\EnumerationSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntersectionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MetaType as MetaTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\UnionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\MetaTypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\NamedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\SupertypeChecker;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Feature\Hydrator\HydrationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationRequest;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;

final readonly class MetaType implements MetaTypeInterface, SupertypeChecker, JsonSerializable {
	public function __construct(
		public MetaTypeValue $value
	) {}

	public function __toString(): string {
		return $this->value->value;
	}

	public function hydrate(HydrationRequest $request): HydrationSuccess|HydrationFailure {
		return $request->withError(
			sprintf("Values of cannot be hydrated to type %s.", $this->value->value),
			$this
		);
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

	public function validate(ValidationRequest $request): ValidationResult {
		return $request->ok();
	}

	public function jsonSerialize(): array {
		return [
			'type' => 'MetaType',
			'metaType' => $this->value->value
		];
	}
}