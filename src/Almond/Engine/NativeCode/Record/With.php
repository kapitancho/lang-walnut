<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Record;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase\WithMethod;

/** @extends WithMethod<RecordType, RecordType|MapType, RecordValue, RecordValue> */
final readonly class With extends WithMethod {

	protected function getValidator(): callable {
		return function(RecordType $targetType, RecordType|MapType $parameterType, mixed $origin): Type|ValidationFailure {
			if ($parameterType instanceof RecordType) {
				return $this->getCombinedRecordType(
					$targetType,
					$parameterType
				);
			}
			$mapTarget = $targetType->asMapType();
			if ($parameterType instanceof MapType) {
				return $this->getCombinedMapType(
					$mapTarget,
					$parameterType
				);
			}
			// @codeCoverageIgnoreStart
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
				$origin
			);
			// @codeCoverageIgnoreEnd
		};
	}

	protected function getExecutor(): callable {
		return fn(RecordValue $target, RecordValue $parameter): Value =>
			$this->executeMapItem($target, $parameter);
	}
}
