<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BytesType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BytesValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<BytesType, RecordType, BytesValue, RecordValue> */
abstract readonly class BytesPad extends NativeMethod {

	protected function getValidator(): callable {
		return function(BytesType $targetType, RecordType $parameterType): BytesType {
			/** @var IntegerType $lengthType */
			$lengthType = $parameterType->typeOf('length');
			return $this->typeRegistry->bytes(
				max(
					$targetType->range->minLength,
					$lengthType->numberRange->min === MinusInfinity::value ?
						0 : $lengthType->numberRange->min->value
				),
				$targetType->range->maxLength === PlusInfinity::value ||
				$lengthType->numberRange->max === PlusInfinity::value ? PlusInfinity::value :
					max(
						$targetType->range->maxLength,
						$lengthType->numberRange->max->value -
						($lengthType->numberRange->max->inclusive ? 0 : 1)
					),
			);
		};
	}

	protected function isParameterTypeValid(Type $parameterType, callable $validator): bool {
		if (!parent::isParameterTypeValid($parameterType, $validator)) {
			return false;
		}
		/** @var RecordType $parameterType */
		$lengthType = $parameterType->typeOf('length');
		$padBytesType = $parameterType->typeOf('padBytes');
		return $lengthType instanceof IntegerType && $padBytesType instanceof BytesType;
	}

	protected function getExecutor(): callable {
		return function(BytesValue $target, RecordValue $parameter): BytesValue {
			/** @var IntegerValue $length */
			$length = $parameter->valueOf('length');
			/** @var BytesValue $padBytes */
			$padBytes = $parameter->valueOf('padBytes');
			$result = str_pad(
				$target->literalValue,
				(int)(string)$length->literalValue,
				$padBytes->literalValue,
				$this->getPadType()
			);
			return $this->valueRegistry->bytes($result);
		};
	}

	abstract protected function getPadType(): int;

	protected function isParameterValueValid(Value $parameter, callable $executor): bool {
		if (!parent::isParameterValueValid($parameter, $executor)) {
			return false;
		}
		/** @var RecordValue $parameter */
		$length = $parameter->valueOf('length');
		$padBytes = $parameter->valueOf('padBytes');
		return $length instanceof IntegerValue && $padBytes instanceof BytesValue;
	}

}
