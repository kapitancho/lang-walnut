<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class IndexOf implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof TupleType) {
			$targetType = $targetType->asArrayType();
		}
		if ($targetType instanceof ArrayType) {
			$maxLength = $targetType->range->maxLength;
			$returnType = $typeRegistry->integer(0,
				$maxLength === PlusInfinity::value ? $maxLength : max($maxLength - 1, 0));
			return $typeRegistry->result(
				$returnType,
				$typeRegistry->atom(
					new TypeNameIdentifier("ItemNotFound")
				)
			);
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
		$targetValue = $target;
		$parameterValue = $parameter;

		if ($targetValue instanceof TupleValue) {
			$values = $targetValue->values;
			foreach ($values as $index => $value) {
				if ($value->equals($parameterValue)) {
					return ($programRegistry->valueRegistry->integer($index));
				}
			}
			return ($programRegistry->valueRegistry->error(
				$programRegistry->valueRegistry->atom(
					new TypeNameIdentifier('ItemNotFound'),
				)
			));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}