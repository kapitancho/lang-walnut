<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\EnumerationType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MetaType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\MetaTypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\EnumerationValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\TypeNativeMethod;

/** @extends TypeNativeMethod<Type, Type, Value> */
final readonly class WithValues extends TypeNativeMethod {

	protected function validateTargetRefType(Type $targetRefType): null|string {
		return $targetRefType instanceof IntegerType ||
			$targetRefType instanceof RealType ||
			$targetRefType instanceof StringType ||
			$targetRefType instanceof EnumerationType ||
			($targetRefType instanceof MetaType && $targetRefType->value === MetaTypeValue::Enumeration) ?
				null :
				sprintf("Target ref type must be an Integer type, a Real type, a String type or an Enumeration type, got: %s", $targetRefType);
	}

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		/** @var TypeType $targetType */
		$refType = $this->toBaseType($targetType->refType);
		if ($refType instanceof IntegerType) {
			return $parameterType->isSubtypeOf(
				$this->typeRegistry->array($this->typeRegistry->integer(), 1)
			) ? null :
				sprintf(
					"The parameter type %s is not a valid array of Integer values",
					$parameterType
				);
		}
		if ($refType instanceof RealType) {
			return $parameterType->isSubtypeOf(
				$this->typeRegistry->array($this->typeRegistry->real(), 1)
			) ? null :
				sprintf(
					"The parameter type %s is not a valid array of Real values",
					$parameterType
				);
		}
		if ($refType instanceof StringType) {
			return $parameterType->isSubtypeOf(
				$this->typeRegistry->array($this->typeRegistry->string(), 1)
			) ? null :
				sprintf(
					"The parameter type %s is not a valid array of String values",
					$parameterType
				);
		}
		if ($refType instanceof EnumerationType) {
			return $parameterType->isSubtypeOf(
				$this->typeRegistry->array($refType, 1)
			) ? null :
				sprintf(
					"The parameter type %s is not a valid array of %s values",
					$parameterType, $refType
				);
		}
		/** @var MetaType $refType */
		return $parameterType->isSubtypeOf(
			$this->typeRegistry->array($this->typeRegistry->any, 1)
		) ? null :
			sprintf(
				"The parameter type %s is not a valid array of values",
				$parameterType
			);
	}

	protected function getValidator(): callable {
		return function(TypeType $targetType, Type $parameterType, mixed $origin): Type {
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof IntegerType) {
				return $this->typeRegistry->type(
					$this->typeRegistry->metaType(MetaTypeValue::IntegerSubset)
				);
			}
			if ($refType instanceof RealType) {
				return $this->typeRegistry->type(
					$this->typeRegistry->metaType(MetaTypeValue::RealSubset)
				);
			}
			if ($refType instanceof StringType) {
				return $this->typeRegistry->type(
					$this->typeRegistry->metaType(MetaTypeValue::StringSubset)
				);
			}
			if ($refType instanceof EnumerationType) {
				return $this->typeRegistry->type($refType);
			}
			return $this->typeRegistry->result(
				$this->typeRegistry->type(
					$this->typeRegistry->metaType(MetaTypeValue::EnumerationSubset)
				),
				$this->typeRegistry->core->unknownEnumerationValue
			);
		};
	}

	protected function getExecutor(): callable {
		return function(TypeValue $target, Value $parameter): Value {
			$typeValue = $this->toBaseType($target->typeValue);
			if ($typeValue instanceof IntegerType) {
				/** @var list<IntegerValue> $values */
				$values = $parameter->values;
				$result = $this->typeRegistry->integerSubset(
					array_map(fn(IntegerValue $value): Number => $value->literalValue, $values)
				);
				return $this->valueRegistry->type($result);
			}
			if ($typeValue instanceof RealType) {
				/** @var list<RealValue|IntegerValue> $values */
				$values = $parameter->values;
				$result = $this->typeRegistry->realSubset(
					array_map(fn(RealValue|IntegerValue $value): Number => $value->literalValue, $values)
				);
				return $this->valueRegistry->type($result);
			}
			if ($typeValue instanceof StringType) {
				/** @var list<StringValue> $values */
				$values = $parameter->values;
				$result = $this->typeRegistry->stringSubset(
					array_map(fn(StringValue $value): string => $value->literalValue, $values)
				);
				return $this->valueRegistry->type($result);
			}
			if ($typeValue instanceof EnumerationType) {
				/** @var list<EnumerationValue> $values */
				$values = $parameter->values;
				$r = [];
				foreach($values as $value) {
					/** @phpstan-ignore instanceof.alwaysTrue */
					if ($value instanceof EnumerationValue &&
						$value->enumeration->name->identifier === $typeValue->name->identifier) {
						$r[] = $value->name;
					} else {
						return $this->valueRegistry->error(
							$this->valueRegistry->core->unknownEnumerationValue(
								$this->valueRegistry->record([
									'enumeration' => $this->valueRegistry->type($typeValue),
									'value' => $value
								])
							)
						);
					}
				}
				$result = $typeValue->subsetType($r);
				return $this->valueRegistry->type($result);
			}
			// @codeCoverageIgnoreStart
			throw new ExecutionException("Invalid parameter value");
			// @codeCoverageIgnoreEnd
		};
	}

}
