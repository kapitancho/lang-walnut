<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\MetaType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class WithRestType implements NativeMethod {

	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		TypeInterface $targetType,
		TypeInterface $parameterType,
	): TypeInterface {
		if ($targetType instanceof TypeType) {
			$refType = $this->toBaseType($targetType->refType);
			if ($parameterType->isSubtypeOf(
				$typeRegistry->type(
					$typeRegistry->any
				)
			)) {
				if ($refType instanceof TupleType || (
					$refType instanceof MetaType && $refType->value === MetaTypeValue::Tuple
				)) {
					return $typeRegistry->type(
						$typeRegistry->metaType(
							MetaTypeValue::Tuple
						)
					);
				}
				if ($refType instanceof RecordType || (
					$refType instanceof MetaType && $refType->value === MetaTypeValue::Record
				)) {
					return $typeRegistry->type(
						$typeRegistry->metaType(
							MetaTypeValue::Record
						)
					);
				}
				// @codeCoverageIgnoreStart
				throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
				// @codeCoverageIgnoreEnd
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
		if ($target instanceof TypeValue) {
			$typeValue = $this->toBaseType($target->typeValue);
			if ($parameter->type->isSubtypeOf(
				$programRegistry->typeRegistry->type(
					$programRegistry->typeRegistry->any
				)
			)) {
				if ($typeValue instanceof TupleType || (
					$typeValue instanceof MetaType && $typeValue->value === MetaTypeValue::Tuple
				)) {
					$result = $programRegistry->typeRegistry->tuple(
						$typeValue instanceof TupleType ? $typeValue->types : [],
						$parameter->typeValue,
					);
					return $programRegistry->valueRegistry->type($result);
				}
				if ($typeValue instanceof RecordType || (
					$typeValue instanceof MetaType && $typeValue->value === MetaTypeValue::Record
				)) {
					$result = $programRegistry->typeRegistry->record(
						$typeValue instanceof RecordType ? $typeValue->types : [],
						$parameter->typeValue,
					);
					return $programRegistry->valueRegistry->type($result);
				}
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}