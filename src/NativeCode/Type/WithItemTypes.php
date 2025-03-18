<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\MetaType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class WithItemTypes implements NativeMethod {

	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		TypeInterface $targetType,
		TypeInterface $parameterType,
	): TypeInterface {
		if ($targetType instanceof TypeType) {
			$refType = $this->toBaseType($targetType->refType);
			if ($parameterType->isSubtypeOf(
				$programRegistry->typeRegistry->array(
					$programRegistry->typeRegistry->type(
						$programRegistry->typeRegistry->any
					)
				)
			)) {
				if ($refType instanceof TupleType || (
					$refType instanceof MetaType && $refType->value === MetaTypeValue::Tuple
				)) {
					return $programRegistry->typeRegistry->type(
						$programRegistry->typeRegistry->metaType(
							MetaTypeValue::Tuple
						)
					);
				}
				// @codeCoverageIgnoreStart
				throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
				// @codeCoverageIgnoreEnd
			}
			if ($parameterType->isSubtypeOf(
				$programRegistry->typeRegistry->map(
					$programRegistry->typeRegistry->type(
						$programRegistry->typeRegistry->any
					)
				)
			)) {
				if ($refType instanceof RecordType || (
					$refType instanceof MetaType && $refType->value === MetaTypeValue::Record
				)) {
					return $programRegistry->typeRegistry->type(
						$programRegistry->typeRegistry->metaType(
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
		ProgramRegistry        $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		$targetValue = $target;

		if ($targetValue instanceof TypeValue) {
			$typeValue = $this->toBaseType($targetValue->typeValue);
			if ($parameter->type->isSubtypeOf(
				$programRegistry->typeRegistry->array(
					$programRegistry->typeRegistry->type(
						$programRegistry->typeRegistry->any
					)
				)
			)) {
				if ($typeValue instanceof TupleType) {
					$result = $programRegistry->typeRegistry->tuple(
						array_map(fn(TypeValue $tv): TypeInterface => $tv->typeValue, $parameter->values),
						$typeValue->restType,
					);
					return ($programRegistry->valueRegistry->type($result));
				}
			}
			if ($parameter->type->isSubtypeOf(
				$programRegistry->typeRegistry->map(
					$programRegistry->typeRegistry->type(
						$programRegistry->typeRegistry->any
					)
				)
			)) {
				if ($typeValue instanceof RecordType) {
					$result = $programRegistry->typeRegistry->record(
						array_map(fn(TypeValue $tv): TypeInterface => $tv->typeValue, $parameter->values),
						$typeValue->restType,
					);
					return ($programRegistry->valueRegistry->type($result));
				}
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}