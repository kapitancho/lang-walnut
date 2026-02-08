<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;

/**
 * @template TItemType of Type
 * @template TParameterType of Type
 * @template TParameterValue of Value
 * @extends NativeMethod<MapType, TParameterType, RecordValue, TParameterValue>
 */
abstract readonly class MapNativeMethod extends NativeMethod {

	public function validate(Type $targetType, Type $parameterType, ?Expression $origin): ValidationSuccess|ValidationFailure {
		$baseTargetType = $this->toBaseType($targetType);
		if ($baseTargetType instanceof RecordType) {
			$targetType = $baseTargetType->asMapType();
		}
		return parent::validate($targetType, $parameterType, $origin);
	}

	protected function isTargetTypeValid(Type $targetType, callable $validator, Expression|null $origin): bool {
		$targetType = $targetType instanceof RecordType ? $targetType->asMapType() : $targetType;
		if (!parent::isTargetTypeValid($targetType, $validator, $origin)) {
			return false;
		}
		/** @var MapType $targetType */
		return $this->isTargetItemTypeValid(
			$this->toBaseType($targetType->itemType),
			$origin
		);
	}

	protected function isTargetItemTypeValid(Type $targetItemType, Expression|null $origin): bool {
		return true;
	}

}
