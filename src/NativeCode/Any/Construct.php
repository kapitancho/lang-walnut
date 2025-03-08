<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\NamedType;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\OpenType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\SubsetType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Type\UserType;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Implementation\Code\NativeCode\ValueConstructor;
use Walnut\Lang\Implementation\Type\Helper\TupleAsRecord;

final readonly class Construct implements NativeMethod {
	use TupleAsRecord;

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType
	): Type {
		if ($parameterType instanceof TypeType) {
			$t = $parameterType->refType;
			return new ValueConstructor()->analyseConstructor(
				$programRegistry,
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
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		//$targetValue = $target->value;
		$parameterValue = $parameter->value;

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