<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\IntersectionType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\NativeCode\ValueConverter;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Shape implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof IntersectionType) {
			foreach($targetType->types as $type) {
				try {
					return $this->analyse($typeRegistry, $methodFinder, $type, $parameterType);
				} catch (AnalyserException) {}
			}
		}
		$parameterType = $this->toBaseType($parameterType);
		if ($parameterType instanceof TypeType) {
			return new ValueConverter()->analyseConvertValueToShape(
				$typeRegistry,
				$methodFinder,
				$targetType,
				$parameterType->refType
			);
		}
		throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
	}

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		//$targetType = $this->toBaseType($target->type);
		if ($parameter instanceof TypeValue) {
			$toType = $parameter->typeValue;
			return new ValueConverter()->convertValueToShape(
				$programRegistry,
				$target,
				$toType
			);
		}

		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}