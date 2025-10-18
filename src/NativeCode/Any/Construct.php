<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Implementation\Code\NativeCode\ValueConstructor;
use Walnut\Lang\Implementation\Type\Helper\TupleAsRecord;

final readonly class Construct implements NativeMethod {
	use TupleAsRecord;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType
	): Type {
		if ($parameterType instanceof TypeType) {
			$t = $parameterType->refType;
			return new ValueConstructor()->analyseConstructor(
				$typeRegistry,
				$methodFinder,
				$t,
				$targetType
			);
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(
			sprintf("Invalid parameter type: %s", $parameterType)
		);
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		//$targetValue = $target->value;
		$parameterValue = $parameter;

		if ($parameterValue instanceof TypeValue) {
			$t = $parameterValue->typeValue;

			return new ValueConstructor()->executeConstructor(
				$programRegistry,
				$t,
				$target
			);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target/parameter value");
		// @codeCoverageIgnoreEnd
	}
}