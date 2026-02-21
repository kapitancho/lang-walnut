<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase\MapFilterBase;

final readonly class Partition extends MapFilterBase {

	protected function getValidator(): callable {
		return function(MapType $targetType, FunctionType $parameterType, mixed $origin): Type {
			$partitionType = $this->typeRegistry->map($targetType->itemType, 0, $targetType->range->maxLength);
			return $this->typeRegistry->record([
				'matching' => $partitionType,
				'notMatching' => $partitionType
			], null);
		};
	}

	protected function getExecutor(): callable {
		return function(RecordValue $target, FunctionValue $parameter): RecordValue {
			$matching = [];
			$notMatching = [];
			$true = $this->valueRegistry->true;

			foreach($target->values as $k => $value) {
				$r = $parameter->execute($value);
				if ($true->equals($r)) {
					$matching[$k] = $value;
				} else {
					$notMatching[$k] = $value;
				}
			}

			return $this->valueRegistry->record([
				'matching' => $this->valueRegistry->record($matching),
				'notMatching' => $this->valueRegistry->record($notMatching)
			]);
		};
	}

}
