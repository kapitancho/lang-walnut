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
use Walnut\Lang\Blueprint\Type\IntersectionType;
use Walnut\Lang\Blueprint\Type\ShapeType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\UnionType;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\OpenValue;
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
		if ($targetType instanceof ShapeType) {
			return $targetType->refType;
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	private function isError($result): bool {
		return
			$result->value instanceof ErrorValue &&
			$result->value->errorValue instanceof OpenValue &&
			$result->value->errorValue->type->name->equals(
				new TypeNameIdentifier('CastNotAvailable')
			);
	}

	public function execute(
		ProgramRegistry $programRegistry,
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetType = $this->toBaseType($target->type);
		if ($targetType instanceof ShapeType) {
			if ($target->value->type->isSubtypeOf($targetType->refType)) {
				return $target;
			}
			$tv = $target->value;
			if ($tv instanceof OpenValue && $tv->type->valueType->isSubtypeOf($targetType->refType)) {
				return TypedValue::forValue($tv->value);
			}
			$method = $programRegistry->methodFinder->methodForType(
				$targetType,
				new MethodNameIdentifier('castAs')
			);
			if ($method instanceof Method) {
				$parameterTypes = $targetType->refType instanceof UnionType ?
					$targetType->refType->types : [$targetType->refType];
				foreach($parameterTypes as $pType) {
					$result = $method->execute(
						$programRegistry,
						TypedValue::forValue($target->value),
						TypedValue::forValue(
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
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}