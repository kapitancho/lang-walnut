<?php

namespace Walnut\Lang\NativeCode\Boolean;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\BooleanType;
use Walnut\Lang\Blueprint\Type\FalseType;
use Walnut\Lang\Blueprint\Type\TrueType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\BooleanValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class BinaryBitwiseXor implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof BooleanType || $targetType instanceof TrueType || $targetType instanceof FalseType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof BooleanType || $parameterType instanceof TrueType || $parameterType instanceof FalseType) {
				return match(true) {
					($targetType instanceof FalseType && $parameterType instanceof FalseType) ||
					($targetType instanceof TrueType && $parameterType instanceof TrueType) => $typeRegistry->false,
					($targetType instanceof FalseType && $parameterType instanceof TrueType) ||
					($targetType instanceof TrueType && $parameterType instanceof FalseType) => $typeRegistry->true,
					default => $typeRegistry->boolean
				};
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
		if ($target instanceof BooleanValue) {
			if ($parameter instanceof BooleanValue) {
				return $programRegistry->valueRegistry->boolean(
					($target->literalValue && !$parameter->literalValue) ||
					(!$target->literalValue && $parameter->literalValue)
				);
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}
