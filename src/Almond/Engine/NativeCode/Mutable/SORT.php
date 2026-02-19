<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Mutable;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\MutableValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\Composite\SortHelper;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MutableNativeMethod;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends MutableNativeMethod<ArrayType|SetType|MapType, Type, Value> */
final readonly class SORT extends MutableNativeMethod {

	protected function validateTargetValueType(Type $valueType): null|string {
		return $valueType instanceof ArrayType || $valueType instanceof MapType || $valueType instanceof SetType ?
			null : sprintf(
				"The value type of the target must be an Array, Map or Set type, got %s",
				$valueType
			);
	}

	protected function getValidator(): callable {
		return function(MutableType $targetType, Type $parameterType, mixed $origin): ValidationSuccess|ValidationFailure {
			/** @var ArrayType|MapType|SetType $valueType */
			$valueType = $this->toBaseType($targetType->valueType);
			$sortHelper = new SortHelper(
				$this->validationFactory,
				$this->typeRegistry,
				$this->valueRegistry
			);
			return $sortHelper->validate(
				$targetType,
				$valueType,
				$parameterType,
				$origin
			);
		};
	}

	protected function getExecutor(): callable {
		return function(MutableValue $target, Value $parameter): MutableValue {
			$v = $target->value;
			if ($v instanceof TupleValue || $v instanceof RecordValue || $v instanceof SetValue) {
				$sortHelper = new SortHelper(
					$this->validationFactory,
					$this->typeRegistry,
					$this->valueRegistry
				);
				$result = $sortHelper->execute(
					$v,
					$parameter,
					match(true) {
						$v instanceof TupleValue => fn(array $values) => $this->valueRegistry->tuple($values),
						$v instanceof RecordValue => fn(array $values) => $this->valueRegistry->record($values),
						$v instanceof SetValue => fn(array $values) => $this->valueRegistry->set($values),
					},
				);
				$target->value = $result;
				return $target;
			}
			return $target;
		};
	}

}
