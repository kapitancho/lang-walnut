<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Type\MetaTypeValue;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class WithItemTypes implements NativeMethod {

	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		TypeInterface $targetType,
		TypeInterface $parameterType,
	): TypeInterface {
		if ($targetType instanceof TypeType) {
			$refType = $this->toBaseType($targetType->refType());
			if ($parameterType->isSubtypeOf(
				$this->context->typeRegistry()->array(
					$this->context->typeRegistry()->type(
						$this->context->typeRegistry()->any()
					)
				)
			)) {
				if ($refType instanceof TupleType) {
					return $this->context->typeRegistry()->type(
						$this->context->typeRegistry()->metaType(
							MetaTypeValue::Tuple
						)
					);
				}
				// @codeCoverageIgnoreStart
				throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
				// @codeCoverageIgnoreEnd
			}
			if ($parameterType->isSubtypeOf(
				$this->context->typeRegistry()->map(
					$this->context->typeRegistry()->type(
						$this->context->typeRegistry()->any()
					)
				)
			)) {
				if ($refType instanceof RecordType) {
					return $this->context->typeRegistry()->type(
						$this->context->typeRegistry()->metaType(
							MetaTypeValue::Record
						)
					);
				}
				// @codeCoverageIgnoreStart
				throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
				// @codeCoverageIgnoreEnd
			}
			// @codeCoverageIgnoreStart
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;

		if ($targetValue instanceof TypeValue) {
			$typeValue = $this->toBaseType($targetValue->typeValue());
			if ($parameter->type->isSubtypeOf(
				$this->context->typeRegistry()->array(
					$this->context->typeRegistry()->type(
						$this->context->typeRegistry()->any()
					)
				)
			)) {
				if ($typeValue instanceof TupleType) {
					$result = $this->context->typeRegistry()->tuple(
						array_map(fn(TypeValue $tv): TypeInterface => $tv->typeValue(), $parameter->value->values()),
						$typeValue->restType(),
					);
					return TypedValue::forValue($this->context->valueRegistry()->type($result));
				}
			}
			if ($parameter->type->isSubtypeOf(
				$this->context->typeRegistry()->map(
					$this->context->typeRegistry()->type(
						$this->context->typeRegistry()->any()
					)
				)
			)) {
				if ($typeValue instanceof RecordType) {
					$result = $this->context->typeRegistry()->record(
						array_map(fn(TypeValue $tv): TypeInterface => $tv->typeValue(), $parameter->value->values()),
						$typeValue->restType(),
					);
					return TypedValue::forValue($this->context->valueRegistry()->type($result));
				}
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}