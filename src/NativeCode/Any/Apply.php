<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseTypeHelper;
use Walnut\Lang\Implementation\Type\Helper\TupleAsRecord;

final readonly class Apply implements NativeMethod {

	use BaseTypeHelper;
	use TupleAsRecord;

	public function analyse(TypeRegistry $typeRegistry, MethodFinder $methodFinder, Type $targetType, Type $parameterType): Type {
		$baseParameterType = $this->toTargetBaseType(
			$parameterType,
			$typeRegistry->metaType(MetaTypeValue::Function)
		);
		if (!$baseParameterType) {
			// @codeCoverageIgnoreStart
			throw new AnalyserException(
				sprintf("Invalid parameter type: %s, expected a function", $parameterType));
			// @codeCoverageIgnoreEnd
		}
		$p = $baseParameterType->parameterType;
		$baseTargetType = $this->adjustParameterType(
			$typeRegistry,
			$p,
			$parameterType,
		);
		if (!$targetType->isSubtypeOf($p)) {
			throw new AnalyserException(sprintf("Invalid target type: %s, %s expected (parameter is %s)",
				$baseTargetType,
				$p,
				$parameterType
			));
		}

		/** @var FunctionType $baseTargetType */
		return $baseParameterType->returnType;
	}

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		$v = $parameter;
		if (!($v instanceof FunctionValue)) {
			// @codeCoverageIgnoreStart
			throw new ExecutionException(
				sprintf("Invalid target value: %s, expected a function", $target->type)
			);
			// @codeCoverageIgnoreEnd
		}
		$target = $this->adjustParameterValue(
			$programRegistry->valueRegistry,
			$v->type->parameterType,
			$target,
		);
		return $v->execute($programRegistry->executionContext, $target);
	}
}