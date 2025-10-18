<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\IntersectionType;
use Walnut\Lang\Blueprint\Type\MetaType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Type\UnionType;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class ItemTypes implements NativeMethod {

	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		TypeInterface $targetType,
		TypeInterface $parameterType,
	): TypeInterface {
		if ($targetType instanceof IntersectionType) {
			foreach($targetType->types as $type) {
				try {
					return $this->analyse($programRegistry, $type, $parameterType);
				} catch (AnalyserException) {}
			}
		}
		if ($targetType instanceof TypeType) {
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof TupleType) {
				return $programRegistry->typeRegistry->tuple(
					array_map(
						fn(TypeInterface $type) => $programRegistry->typeRegistry->type($type),
						$refType->types,
					)
				);
			}
			if ($refType instanceof RecordType) {
				return $programRegistry->typeRegistry->record(
					array_map(
						fn(TypeInterface $type) => $programRegistry->typeRegistry->type($type),
						$refType->types,
					)
				);
			}
			if ($refType instanceof MetaType) {
				if (in_array($refType->value, [
					MetaTypeValue::Tuple, MetaTypeValue::Union, MetaTypeValue::Intersection
				], true)) {
					return $programRegistry->typeRegistry->array(
						$programRegistry->typeRegistry->type(
							$programRegistry->typeRegistry->any
						)
					);
				}
				if ($refType->value === MetaTypeValue::Record) {
					return $programRegistry->typeRegistry->map(
						$programRegistry->typeRegistry->type(
							$programRegistry->typeRegistry->any
						)
					);
				}
			}
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
		$targetValue = $target;

		if ($targetValue instanceof TypeValue) {
			$typeValue = $this->toBaseType($targetValue->typeValue);
			if ($typeValue instanceof TupleType || $typeValue instanceof UnionType || $typeValue instanceof IntersectionType) {
				return ($programRegistry->valueRegistry->tuple(
					array_map(
						fn(TypeInterface $type) => $programRegistry->valueRegistry->type($type),
						$typeValue->types
					)
				));
			}
			if ($typeValue instanceof RecordType) {
				return ($programRegistry->valueRegistry->record(
					array_map(
						fn(TypeInterface $type) => $programRegistry->valueRegistry->type($type),
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