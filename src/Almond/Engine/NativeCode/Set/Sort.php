<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Set;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\Composite\SortHelper;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\SetNativeMethod;

/** @extends SetNativeMethod<Type, Type, Value> */
final readonly class Sort extends SetNativeMethod {

	protected function getValidator(): callable {
		return function(SetType $targetType, Type $parameterType, mixed $origin): ValidationSuccess|ValidationFailure {
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
		return function(SetValue $target, Value $parameter): Value {
			$sortHelper = new SortHelper(
				$this->validationFactory,
				$this->typeRegistry,
				$this->valueRegistry
			);
			return $sortHelper->execute(
				$target,
				$parameter,
				fn(array $values) =>
					/** @phpstan-ignore arrayValues.list */
					$this->valueRegistry->set(array_values($values))
			);
		};
	}
}
