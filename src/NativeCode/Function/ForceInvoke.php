<?php

namespace Walnut\Lang\NativeCode\Function;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
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

final readonly class ForceInvoke implements NativeMethod {

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

		/** @var FunctionType $baseTargetType */
		return $typeRegistry->result(
			$baseTargetType->returnType,
			$typeRegistry->typeByName(new TypeNameIdentifier('InvocationError'))
		);
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
		if (!$parameter->type->isSubtypeOf($v->type->parameterType)) {
			return $programRegistry->valueRegistry->error(
				$programRegistry->valueRegistry->dataValue(
					new TypeNameIdentifier("InvocationError"),
					$programRegistry->valueRegistry->record([
						'functionType' => $programRegistry->valueRegistry->type($v->type),
						'providedParameterType' => $programRegistry->valueRegistry->type($parameter->type),
						'errorMessage' => $programRegistry->valueRegistry->string(
							sprintf(
								"Invalid parameter type: %s, %s expected (function is %s)",
								$parameter->type,
								$v->type->parameterType,
								$v->type
							)
						),
					])
				)
			);
		}
		return $v->execute($programRegistry->executionContext, $parameter);
	}
}