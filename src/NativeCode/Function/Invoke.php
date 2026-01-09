<?php

namespace Walnut\Lang\NativeCode\Function;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseTypeHelper;
use Walnut\Lang\Implementation\Type\Helper\TupleAsRecord;

final readonly class Invoke implements NativeMethod {

	use BaseTypeHelper;
	use TupleAsRecord;

	public function analyse(TypeRegistry $typeRegistry, MethodAnalyser $methodAnalyser, Type $targetType, Type $parameterType): Type {
		$baseTargetType = $this->toTargetBaseType(
			$targetType,
			$typeRegistry->metaType(MetaTypeValue::Function)
		);
		if (!$baseTargetType) {
			// @codeCoverageIgnoreStart
			throw new AnalyserException(
				sprintf("Invalid target type: %s, expected a function", $targetType));
			// @codeCoverageIgnoreEnd
		}
		$p = $baseTargetType->parameterType;
		$parameterType = $this->adjustParameterType(
			$typeRegistry,
			$p,
			$parameterType,
		);
		if (!$parameterType->isSubtypeOf($p)) {
			throw new AnalyserException(sprintf("Invalid parameter type: %s, %s expected (target is %s)",
				$parameterType,
				$p,
				$targetType
			));
		}

		/** @var FunctionType $baseTargetType */
		return $baseTargetType->returnType;
	}

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		$v = $target;
		if (!($v instanceof FunctionValue)) {
			// @codeCoverageIgnoreStart
			throw new ExecutionException(
				sprintf("Invalid target value: %s, expected a function", $target->type)
			);
			// @codeCoverageIgnoreEnd
		}
		$parameter = $this->adjustParameterValue(
			$programRegistry->valueRegistry,
			$v->type->parameterType,
			$parameter,
		);
		return $v->execute($programRegistry->executionContext, $parameter);
	}
}