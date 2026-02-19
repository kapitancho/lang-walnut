<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase\MapFilterKeyValueBase;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\ResultType;

final readonly class FindFirstKeyValue extends MapFilterKeyValueBase {

	protected function getValidator(): callable {
		return function(MapType $targetType, FunctionType $parameterType, mixed $origin): ResultType {
			$kv = $this->typeRegistry->record([
				'key' => $targetType->keyType,
				'value' => $targetType->itemType
			], null);

			return $this->typeRegistry->result(
				$kv,
				$this->typeRegistry->core->itemNotFound
			);
		};
	}

	protected function getExecutor(): callable {
		return function(RecordValue $target, FunctionValue $parameter): Value {
			$true = $this->valueRegistry->true;
			foreach($target->values as $key => $value) {
				$val = $this->valueRegistry->record([
					'key' => $this->valueRegistry->string($key),
					'value' => $value
				]);
				$filterResult = $parameter->execute($val);
				if ($filterResult->equals($true)) {
					return $val;
				}
			}
			return $this->valueRegistry->error(
				$this->valueRegistry->core->itemNotFound
			);
		};
	}

}
