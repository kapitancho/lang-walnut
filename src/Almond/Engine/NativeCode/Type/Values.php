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

	protected function validateTargetRefType(Type $targetRefType): null|string {
		if ($targetRefType instanceof MetaType) {
			return match($targetRefType->value) {
				MetaTypeValue::Enumeration, MetaTypeValue::EnumerationSubset,
				MetaTypeValue::IntegerSubset, MetaTypeValue::RealSubset,
				MetaTypeValue::StringSubset => null,
				default => sprintf(
					"Target ref type must be a EnumerationSubset, IntegerSubset, RealSubset or StringSubset type, got: %s",
					$targetRefType
				)
			};
		}
		return $targetRefType instanceof IntegerSubsetType ||
			$targetRefType instanceof RealSubsetType ||
			$targetRefType instanceof StringSubsetType ||
			$targetRefType instanceof EnumerationSubsetType ?
				null : sprintf(
					"Target ref type must be a EnumerationSubset, IntegerSubset, RealSubset or StringSubset type, got: %s",
					$targetRefType
					);
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
