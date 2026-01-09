<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\MetaType;
use Walnut\Lang\Blueprint\Type\MutableType;
use Walnut\Lang\Blueprint\Type\OptionalKeyType;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class WithValueType implements NativeMethod {

	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
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
				if ($refType instanceof MutableType || (
					$refType instanceof MetaType && $refType->value === MetaTypeValue::MutableValue
				)) {
					return $typeRegistry->type(
						$typeRegistry->metaType(MetaTypeValue::MutableValue)
					);
				}
				if ($refType instanceof OptionalKeyType) {
					return $typeRegistry->type(
						$typeRegistry->optionalKey($parameterType->refType)
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
				/** @var TypeValue $parameter */
				if ($typeValue instanceof MutableType || (
					$typeValue instanceof MetaType && $typeValue->value === MetaTypeValue::MutableValue
				)) {
					$result = $programRegistry->typeRegistry->mutable(
						$parameter->typeValue,
					);
					return $programRegistry->valueRegistry->type($result);
				}
				if ($typeValue instanceof OptionalKeyType) {
					$result = $programRegistry->typeRegistry->optionalKey(
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