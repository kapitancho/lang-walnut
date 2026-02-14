<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntersectionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MetaType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\MetaTypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Method\Native\ItemHelper;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<MapType|RecordType, Type, RecordValue, Value> */
final readonly class Item extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool|Type {
		return $targetType instanceof IntersectionType ||
			$targetType instanceof RecordType ||
			$targetType instanceof MapType || (
				$targetType instanceof MetaType && $targetType->value === MetaTypeValue::Record
			);
	}

	protected function getValidator(): callable {
		return function(Type $targetType, Type $parameterType, mixed $origin): ValidationSuccess|ValidationFailure {
			$itemHelper = new ItemHelper(
				$this->validationFactory,
				$this->typeRegistry,
				$this->valueRegistry
			);
			return $itemHelper->validateMapItem($targetType, $parameterType, $origin);
		};
	}

	protected function getExecutor(): callable {
		return function(RecordValue $target, Value $parameter): Value {
			$itemHelper = new ItemHelper(
				$this->validationFactory,
				$this->typeRegistry,
				$this->valueRegistry
			);
			return $itemHelper->executeMapItem($target, $parameter);
		};
	}

}
