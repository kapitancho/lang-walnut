<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Implementation\Value\StringValue;

final readonly class AsInteger implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof StringType) {
			return $typeRegistry->result(
				$typeRegistry->integer(),
				$typeRegistry->core->notANumber
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
		if ($target instanceof StringValue) {
			return (string)($result = (int)$target->literalValue) === $target->literalValue ?
				$programRegistry->valueRegistry->integer($result) :
				$programRegistry->valueRegistry->error(
					$programRegistry->valueRegistry->core->notANumber
				);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}