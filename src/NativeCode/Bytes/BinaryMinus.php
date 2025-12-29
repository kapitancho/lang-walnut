<?php

namespace Walnut\Lang\NativeCode\Bytes;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\BytesType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\BytesValue;
use Walnut\Lang\Blueprint\Value\SetValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class BinaryMinus implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof BytesType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType->isSubtypeOf(
				$typeRegistry->union([
					$typeRegistry->bytes(1),
					$typeRegistry->array(
						$typeRegistry->bytes(1)
					),
					$typeRegistry->set(
						$typeRegistry->bytes(1)
					)
				])
			)) {
				return $typeRegistry->bytes(
					0,
					$targetType->range->maxLength
				);
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
			$values = [];
			if ($parameter instanceof BytesValue) {
				$values[] = $parameter->literalValue;
			} elseif ($parameter instanceof TupleValue || $parameter instanceof SetValue) {
				foreach ($parameter->values as $item) {
					if (!($item instanceof BytesValue)) {
						// @codeCoverageIgnoreStart
						throw new ExecutionException("Invalid parameter type");
						// @codeCoverageIgnoreEnd
					}
					$values[] = $item->literalValue;
				}
			}
			return $programRegistry->valueRegistry->bytes(
				str_replace($values, '', $target->literalValue)
			);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}