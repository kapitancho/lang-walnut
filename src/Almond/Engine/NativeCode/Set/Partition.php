<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Set;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\FunctionValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase\SetFilterBase;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\SetNativeMethod;

/** @extends SetNativeMethod<FunctionType, FunctionValue> */
final readonly class Partition extends SetFilterBase {

	protected function getValidator(): callable {
		return function(SetType $targetType, FunctionType $parameterType, mixed $origin): Type {
			$pType = $this->toBaseType($parameterType->returnType);
			$partitionType = $this->typeRegistry->set($targetType->itemType, 0, $targetType->range->maxLength);
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
		return function(SetValue $target, FunctionValue $parameter): RecordValue|ErrorValue {
			$matching = [];
			$notMatching = [];
			$true = $this->valueRegistry->true;

			foreach ($target->values as $value) {
				$r = $parameter->execute($value);
				if ($r instanceof ErrorValue) {
					return $r;
				}
				if ($true->equals($r)) {
					$matching[] = $value;
				} else {
					$notMatching[] = $value;
				}
			}

			return $this->valueRegistry->record([
				'matching' => $this->valueRegistry->set($matching),
				'notMatching' => $this->valueRegistry->set($notMatching)
			]);
		};
	}

}
