<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Type\IntersectionType;
use Walnut\Lang\Blueprint\Type\MetaType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Type\UnionType;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class ItemTypes implements NativeMethod {

	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		TypeInterface $targetType,
		TypeInterface $parameterType,
	): TypeInterface {
		if ($targetType instanceof TypeType) {
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof TupleType) {
				return $this->context->typeRegistry->tuple(
					array_map(
						fn(TypeInterface $type) => $this->context->typeRegistry->type($type),
						$refType->types,
					)
				);
			}
			if ($refType instanceof RecordType) {
				return $this->context->typeRegistry->record(
					array_map(
						fn(TypeInterface $type) => $this->context->typeRegistry->type($type),
						$refType->types,
					)
				);
			}
			if ($refType instanceof MetaType) {
				if (in_array($refType->value, [
					MetaTypeValue::Tuple, MetaTypeValue::Union, MetaTypeValue::Intersection
				], true)) {
					return $this->context->typeRegistry->array(
						$this->context->typeRegistry->type(
							$this->context->typeRegistry->any
						)
					);
				}
				if ($refType->value === MetaTypeValue::Record) {
					return $this->context->typeRegistry->map(
						$this->context->typeRegistry->type(
							$this->context->typeRegistry->any
						)
					);
				}
			}
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;

		if ($targetValue instanceof TypeValue) {
			$typeValue = $this->toBaseType($targetValue->typeValue);
			if ($typeValue instanceof TupleType || $typeValue instanceof UnionType || $typeValue instanceof IntersectionType) {
				return TypedValue::forValue($this->context->valueRegistry->tuple(
					array_map(
						fn(TypeInterface $type) => $this->context->valueRegistry->type($type),
						$typeValue->types
					)
				));
			}
			if ($typeValue instanceof RecordType) {
				return TypedValue::forValue($this->context->valueRegistry->record(
					array_map(
						fn(TypeInterface $type) => $this->context->valueRegistry->type($type),
						$typeValue->types
					)
				));
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}