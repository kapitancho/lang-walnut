<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\NamedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;

/**
 * @template TTargetType of NamedType
 * @template TParameterType of Type
 * @template TTargetValue of Value
 * @template TParameterValue of Value
 * @extends NativeMethod<TTargetType, TParameterType, TTargetValue, TParameterValue>
 */
abstract readonly class NamedNativeMethod extends NativeMethod {

	protected function validateTargetType(Type $targetType, mixed $origin): null|string {
		if ($targetType instanceof NamedType) {
			$targetTypeName = $targetType->name;
			$expectedTypeName = $this->getExpectedTypeName();
			if (!$targetTypeName->equals($expectedTypeName)) {
				return sprintf("The target type must be %s, got %s",
					$expectedTypeName, $targetTypeName);
			}
		}
		return null;
	}

	abstract protected function getExpectedTypeName(): TypeName;

	protected function isTargetValueValid(Value $target, callable $executor): bool {
		if (!parent::isTargetValueValid($target, $executor)) {
			return false;
		}
		/** @var NamedType $targetType */
		$targetType = $target->type;
		return $targetType->name->equals($this->getExpectedTypeName());
	}

}