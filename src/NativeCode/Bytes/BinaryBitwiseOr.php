<?php

namespace Walnut\Lang\NativeCode\Bytes;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\BytesType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\BytesValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class BinaryBitwiseOr implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof BytesType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof BytesType) {
				// Result has the length of the longer input (we pad the shorter one)
				$minLength = max($targetType->range->minLength, $parameterType->range->minLength);
				$maxLength = max($targetType->range->maxLength, $parameterType->range->maxLength);
				return $typeRegistry->bytes($minLength, $maxLength);
			}
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		if ($target instanceof BytesValue) {
			if ($parameter instanceof BytesValue) {
				$targetBytes = $target->literalValue;
				$paramBytes = $parameter->literalValue;
				$targetLen = strlen($targetBytes);
				$paramLen = strlen($paramBytes);

				// Pad the shorter one with zeros on the left
				$maxLen = max($targetLen, $paramLen);
				if ($targetLen < $maxLen) {
					$targetBytes = str_pad($targetBytes, $maxLen, "\x00", STR_PAD_LEFT);
				}
				if ($paramLen < $maxLen) {
					$paramBytes = str_pad($paramBytes, $maxLen, "\x00", STR_PAD_LEFT);
				}

				$result = '';
				for ($i = 0; $i < $maxLen; $i++) {
					$result .= chr(ord($targetBytes[$i]) | ord($paramBytes[$i]));
				}

				return $programRegistry->valueRegistry->bytes($result);
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
