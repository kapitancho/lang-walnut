<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class PadRight implements NativeMethod {
	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof StringType || $targetType instanceof StringSubsetType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof RecordType) {
				$types = $parameterType->types;
				$lengthType = $types['length'] ?? null;
				$padStringType = $types['padString'] ?? null;
				if (($lengthType instanceof IntegerType) &&
					($padStringType instanceof StringType || $padStringType instanceof StringSubsetType)
				) {
					return $programRegistry->typeRegistry->string(
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

	public function execute(
		ProgramRegistry        $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		$targetValue = $target;
		$parameterValue = $parameter;

				if ($targetValue instanceof StringValue) {
			if ($parameterValue instanceof RecordValue) {
				$values = $parameterValue->values;
				$length = $values['length'] ?? null;
				$padString = $values['padString'] ?? null;
				if ($length instanceof IntegerValue && $padString instanceof StringValue) {
					$result = str_pad(
						$targetValue->literalValue,
						(string)$length->literalValue,
						$padString->literalValue
                    );
					return ($programRegistry->valueRegistry->string($result));
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