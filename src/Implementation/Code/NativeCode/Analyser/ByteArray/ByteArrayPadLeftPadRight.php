<?php

namespace Walnut\Lang\Implementation\Code\NativeCode\Analyser\ByteArray;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\ByteArrayType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\ByteArrayValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

trait ByteArrayPadLeftPadRight {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof ByteArrayType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof RecordType) {
				$types = $parameterType->types;
				$lengthType = $types['length'] ?? null;
				$padByteArrayType = $types['padByteArray'] ?? null;
				if ($lengthType instanceof IntegerType && $padByteArrayType instanceof ByteArrayType) {
					return $typeRegistry->byteArray(
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
				}
			}
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	private function executeHelper(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter,
		int $padType
	): Value {
		if ($target instanceof ByteArrayValue) {
			if ($parameter instanceof RecordValue) {
				$values = $parameter->values;
				$length = $values['length'] ?? null;
				$padByteArray = $values['padByteArray'] ?? null;
				if ($length instanceof IntegerValue && $padByteArray instanceof ByteArrayValue) {
					$result = str_pad(
						$target->literalValue,
						(int)(string)$length->literalValue,
						$padByteArray->literalValue,
						$padType
					);
					return $programRegistry->valueRegistry->byteArray($result);
				}
			}
			// @codeCoverageIgnoreStart
			throw new ExecutionException("Invalid parameter value");
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}
