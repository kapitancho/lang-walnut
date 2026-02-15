<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\Composite\SortHelper;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

/** @extends ArrayNativeMethod<Type, Type, Value> */
final readonly class Sort extends ArrayNativeMethod {

	protected function getValidator(): callable {
		return function(ArrayType $targetType, Type $parameterType, mixed $origin): ValidationSuccess|ValidationFailure {
			$sortHelper = new SortHelper(
				$this->validationFactory,
				$this->typeRegistry,
				$this->valueRegistry
			);
			return $sortHelper->validate(
				$targetType,
				$targetType,
				$parameterType,
				$origin
			);
		};
	}

	protected function getExecutor(): callable {
		return function(TupleValue $target, Value $parameter): Value {
			$sortHelper = new SortHelper(
				$this->validationFactory,
				$this->typeRegistry,
				$this->valueRegistry
			);
			return $sortHelper->execute(
				$target,
				$parameter,
				fn(array $values) => $this->valueRegistry->tuple(array_values($values))
			);
		};
	}

}
