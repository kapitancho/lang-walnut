<?php

namespace Walnut\Lang\NativeCode\Data;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\DataType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\DataValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\NativeCode\Analyser\Composite\With as WithTrait;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class With implements NativeMethod {
	use BaseType, WithTrait;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType
	): Type {
		$type = $this->toBaseType($targetType);
		if ($type instanceof DataType) {
			return $this->analyseDataOpenType(
				$targetType,
				$parameterType,
				fn(): Type => $targetType,
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
		if ($target instanceof DataValue) {
			return $this->executeDataOpenType(
				$programRegistry,
				$target,
				$parameter,
				static function(Value $parameter) use ($programRegistry, $target) {
					return $programRegistry->valueRegistry->dataValue($target->type->name, $parameter);
				}
			);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}