<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Open;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\OpenValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Method\Native\ItemHelper;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<OpenType, Type, OpenValue, Value> */
final readonly class Item extends NativeMethod {

	protected function getValidator(): callable {
		$itemHelper = new ItemHelper(
			$this->validationFactory,
			$this->typeRegistry,
			$this->valueRegistry
		);
		return fn(OpenType $targetType, Type $parameterType, mixed $origin): ValidationSuccess|ValidationFailure =>
			$itemHelper->validateDataOpenType($targetType, $parameterType, $origin);
	}

	protected function getExecutor(): callable {
		$itemHelper = new ItemHelper(
			$this->validationFactory,
			$this->typeRegistry,
			$this->valueRegistry
		);
		return fn(OpenValue $target, Value $parameter): Value =>
			$itemHelper->executeDataOpenType($target, $parameter);
	}

}
