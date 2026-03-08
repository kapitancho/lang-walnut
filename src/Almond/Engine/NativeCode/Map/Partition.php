<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OptionalKeyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase\MapFilterBase;

final readonly class Partition extends MapFilterBase {

	protected function getValidator(): callable {
		return function(MapType $targetType, FunctionType $parameterType, mixed $origin): Type {
			$recordReturnType = null;
			if ($targetType instanceof RecordType) {
				$recordReturnType = $this->typeRegistry->record(
					array_map(
						fn(Type $type): OptionalKeyType =>
							$type instanceof OptionalKeyType ?
								$type :
								$this->typeRegistry->optionalKey($type),
						$targetType->types
					),
					$targetType->restType
				);
			}
			$pType = $this->toBaseType($parameterType->returnType);
			$partitionType = $recordReturnType ?? $this->typeRegistry->map($targetType->itemType, 0, $targetType->range->maxLength);
			$returnType = $this->typeRegistry->record([
				'matching' => $partitionType,
				'notMatching' => $partitionType
			], null);

			return $pType instanceof ResultType ? $this->typeRegistry->result(
				$returnType,
				$pType->errorType
			) : $returnType;
		};
	}

	protected function getExecutor(): callable {
		return function(RecordValue $target, FunctionValue $parameter): RecordValue|ErrorValue {
			$matching = [];
			$notMatching = [];
			$true = $this->valueRegistry->true;

			foreach($target->values as $k => $value) {
				$r = $parameter->execute($value);
				if ($r instanceof ErrorValue) {
					return $r;
				}
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
