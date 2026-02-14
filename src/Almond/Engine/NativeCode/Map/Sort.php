<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\Composite\SortHelper;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<MapType|RecordType, Type, RecordValue, Value> */
final readonly class Sort extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool|Type {
		return $targetType instanceof MapType || $targetType instanceof RecordType;
	}

	protected function getValidator(): callable {
		return function(MapType|RecordType $targetType, Type $parameterType, mixed $origin): ValidationSuccess|ValidationFailure {
			$type = $targetType instanceof RecordType ? $targetType->asMapType() : $targetType;
			$sortHelper = new SortHelper(
				$this->validationFactory,
				$this->typeRegistry,
				$this->valueRegistry
			);
			return $sortHelper->validate(
				$type,
				$type,
				$parameterType,
				$origin
			);
		};
	}

	protected function getExecutor(): callable {
		return function(RecordValue $target, Value $parameter): Value {
			$sortHelper = new SortHelper(
				$this->validationFactory,
				$this->typeRegistry,
				$this->valueRegistry
			);
			return $sortHelper->execute(
				$target,
				$parameter,
				fn(array $values) => $this->valueRegistry->record($values)
			);
		};
	}

}
