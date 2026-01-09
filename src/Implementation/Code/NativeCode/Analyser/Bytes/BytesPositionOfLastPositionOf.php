<?php

namespace Walnut\Lang\Implementation\Code\NativeCode\Analyser\Bytes;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\BytesType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\BytesValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

trait BytesPositionOfLastPositionOf {
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
				return $typeRegistry->result(
					$typeRegistry->integer(0,
						$targetType->range->maxLength === PlusInfinity::value ? PlusInfinity::value :
						$targetType->range->maxLength - $parameterType->range->minLength
					),
					$typeRegistry->core->sliceNotInBytes
				);
			}
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	/**
	 * @param callable(string, string): (false|int) $posFn
	 */
	private function executeHelper(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter,
		callable $posFn
	): Value {
		if ($target instanceof BytesValue) {
			if ($parameter instanceof BytesValue) {
				$result = $posFn($target->literalValue, $parameter->literalValue);
				return $result === false ?
					$programRegistry->valueRegistry->error(
						$programRegistry->valueRegistry->core->sliceNotInBytes
					) : $programRegistry->valueRegistry->integer($result);
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
