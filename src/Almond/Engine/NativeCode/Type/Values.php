<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\EnumerationSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MetaType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\MetaTypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;

/** @extends TypeNativeMethod<MetaType|IntegerSubsetType|RealSubsetType|StringSubsetType|EnumerationSubsetType, NullType, NullValue> */
final readonly class Values extends TypeNativeMethod {

	protected function isTargetRefTypeValid(Type $targetRefType, mixed $origin): bool {
		$refType = $this->toBaseType($targetRefType);
		if ($refType instanceof MetaType) {
			return match($refType->value) {
				MetaTypeValue::Enumeration, MetaTypeValue::EnumerationSubset,
				MetaTypeValue::IntegerSubset, MetaTypeValue::RealSubset,
				MetaTypeValue::StringSubset => true,
				default => false
			};
		}
		return $refType instanceof IntegerSubsetType ||
			$refType instanceof RealSubsetType ||
			$refType instanceof StringSubsetType ||
			$refType instanceof EnumerationSubsetType;
	}

	protected function getValidator(): callable {
		return function(TypeType $targetType, NullType $parameterType): Type {
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof MetaType) {
				$t = match($refType->value) {
					MetaTypeValue::Enumeration,
					MetaTypeValue::EnumerationSubset
						=> $this->typeRegistry->metaType(MetaTypeValue::Enumeration),
					MetaTypeValue::IntegerSubset => $this->typeRegistry->integer(),
					MetaTypeValue::RealSubset => $this->typeRegistry->real(),
					MetaTypeValue::StringSubset => $this->typeRegistry->string(),
					default => null
				};
				return $this->typeRegistry->array($t, 1);
			}
			/** @var IntegerSubsetType|RealSubsetType|StringSubsetType|EnumerationSubsetType $refType */
			$l = count($refType->subsetValues);
			return $this->typeRegistry->array($refType, $l, $l);
		};
	}

	protected function getExecutor(): callable {
		return function(TypeValue $target, NullValue $parameter): TupleValue {
			$refType = $this->toBaseType($target->typeValue);
			if ($refType instanceof EnumerationSubsetType) {
				return $this->valueRegistry->tuple(
					array_values(array_unique($refType->subsetValues))
				);
			}
			if ($refType instanceof IntegerSubsetType) {
				return $this->valueRegistry->tuple(
					array_map(
						fn(Number $value): IntegerValue => $this->valueRegistry->integer($value),
						array_values(array_unique($refType->subsetValues))
					)
				);
			}
			if ($refType instanceof RealSubsetType) {
				return $this->valueRegistry->tuple(
					array_map(
						fn(Number $value): RealValue => $this->valueRegistry->real($value),
						array_values(array_unique($refType->subsetValues))
					)
				);
			}
			/** @var StringSubsetType $refType */
			return $this->valueRegistry->tuple(
				array_map(
					fn(string $value): StringValue => $this->valueRegistry->string($value),
					array_values(array_unique($refType->subsetValues))
				)
			);
		};
	}

}
