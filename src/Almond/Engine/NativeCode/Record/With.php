<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Record;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase\WithMethod;

/** @extends WithMethod<RecordType, MapType, RecordValue, RecordValue> */
final readonly class With extends WithMethod {

	protected function getValidator(): callable {
		return function(RecordType $targetType, MapType $parameterType): Type {
			if ($parameterType instanceof RecordType) {
				return $this->getCombinedRecordType(
					$targetType,
					$parameterType
				);
			}
			return $this->getCombinedMapType(
				$targetType,
				$parameterType
			);
		};
	}

	protected function getExecutor(): callable {
		return fn(RecordValue $target, RecordValue $parameter): Value =>
			$this->executeMapItem($target, $parameter);
	}
}
