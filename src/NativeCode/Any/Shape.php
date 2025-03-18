<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\IntersectionType;
use Walnut\Lang\Blueprint\Type\ShapeType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Type\UnionType;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\OpenValue;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Shape implements NativeMethod {
	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof IntersectionType) {
			foreach($targetType->types as $type) {
				try {
					return $this->analyse($programRegistry, $type, $parameterType);
				} catch (AnalyserException) {}
			}
		}
		$parameterType = $this->toBaseType($parameterType);
		if ($parameterType instanceof TypeType) {
			if ($parameterType->refType->isSubtypeOf($targetType)) {
				return $parameterType->refType;
			}
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
		}
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
	}

	private function isError(Value $result): bool {
		return
			$result instanceof ErrorValue &&
			$result->errorValue instanceof OpenValue &&
			$result->errorValue->type->name->equals(
				new TypeNameIdentifier('CastNotAvailable')
			);
	}

	public function execute(
		ProgramRegistry        $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		$targetType = $this->toBaseType($target->type);
		if ($parameter instanceof TypeValue) {
			$toType = $parameter->typeValue;
			if ($target->type->isSubtypeOf($toType)) {
				return $target;
			}
			$tv = $target;
			if ($tv instanceof OpenValue && $tv->type->valueType->isSubtypeOf($toType)) {
				return $tv->value;
			}
			$method = $programRegistry->methodFinder->methodForType(
				$targetType,
				new MethodNameIdentifier('castAs')
			);
			if ($method instanceof Method) {
				$parameterTypes = $toType instanceof UnionType ?
					$toType->types : [$toType];
				foreach($parameterTypes as $pType) {
					$result = $method->execute(
						$programRegistry,
						($target),
						(
							$programRegistry->valueRegistry->type($pType)
						)
					);
					if (!$this->isError($result)) {
						return $result;
					}
				}
				/*
					$xResult = $method->execute(
						$programRegistry,
						TypedValue::forValue($target->value),
						TypedValue::forValue(
							$programRegistry->valueRegistry->type($targetType->refType)
						)
					);
				*/
					//if ($this->isError($result) ) {
						// @codeCoverageIgnoreStart
						throw new ExecutionException("Unable to shape the target value");
						// @codeCoverageIgnoreEnd
					//}
					//return $xResult;
			//	}
				//return $result;
			}
		}

		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}