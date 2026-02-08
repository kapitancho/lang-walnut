<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<StringType, RecordType, StringValue, RecordValue> */
abstract readonly class StringPad extends NativeMethod {

	protected function getValidator(): callable {
		return function(StringType $targetType, RecordType $parameterType): StringType {
			/** @var IntegerType $lengthType */
			$lengthType = $parameterType->typeOf('length');
			return $this->typeRegistry->string(
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
		$padStringType = $parameterType->typeOf('padString');
		return $lengthType instanceof IntegerType && $padStringType instanceof StringType;
	}

	protected function getExecutor(): callable {
		return function(StringValue $target, RecordValue $parameter): StringValue {
			/** @var IntegerValue $length */
			$length = $parameter->valueOf('length');
			/** @var StringValue $padString */
			$padString = $parameter->valueOf('padString');
			$result = str_pad(
				$target->literalValue,
				(int)(string)$length->literalValue,
				$padString->literalValue,
				$this->getPadType()
			);
			return $this->valueRegistry->string($result);
		};
	}

	abstract protected function getPadType(): int;

	protected function isParameterValueValid(Value $parameter, callable $executor): bool {
		if (!parent::isParameterValueValid($parameter, $executor)) {
			return false;
		}
		/** @var RecordValue $parameter */
		$length = $parameter->valueOf('length');
		$padString = $parameter->valueOf('padString');
		return $length instanceof IntegerValue && $padString instanceof StringValue;
	}

}
