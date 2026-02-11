<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Data;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\DataType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\DataValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase\WithMethod;

/** @extends WithMethod<DataType, Type, DataValue, Value> */
final readonly class With extends WithMethod {

	protected function getValidator(): callable {
		return fn(DataType $targetType, Type $parameterType, mixed $origin): Type|ValidationFailure =>
			$this->validateDataOpenType(
				$targetType,
				$parameterType,
				static fn(): Type => $targetType,
				$origin
			);
	}

	protected function getExecutor(): callable {
		return fn(DataValue $target, Value $parameter): Value =>
			$this->executeDataOpenType(
				$target,
				$parameter,
				fn(Value $p): Value => $this->valueRegistry->data($target->type->name, $p)
			);
	}
}
