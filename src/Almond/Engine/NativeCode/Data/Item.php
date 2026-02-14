<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Data;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\DataType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\DataValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Method\Native\ItemHelper;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<DataType, Type, DataValue, Value> */
final readonly class Item extends NativeMethod {

	protected function getValidator(): callable {
		$itemHelper = new ItemHelper(
			$this->validationFactory,
			$this->typeRegistry,
			$this->valueRegistry
		);
		return fn(DataType $targetType, Type $parameterType, mixed $origin): ValidationSuccess|ValidationFailure =>
			$itemHelper->validateDataOpenType($targetType, $parameterType, $origin);
	}

	protected function getExecutor(): callable {
		$itemHelper = new ItemHelper(
			$this->validationFactory,
			$this->typeRegistry,
			$this->valueRegistry
		);
		return fn(DataValue $target, Value $parameter): Value =>
			$itemHelper->executeDataOpenType($target, $parameter);
	}

}
