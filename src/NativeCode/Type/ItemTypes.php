<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
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
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		TypeInterface $targetType,
		TypeInterface $parameterType,
	): TypeInterface {
		if ($targetType instanceof IntersectionType) {
			foreach($targetType->types as $type) {
				try {
					return $this->analyse($typeRegistry, $methodFinder, $type, $parameterType);
				// @codeCoverageIgnoreStart
				} catch (AnalyserException) {}
				// @codeCoverageIgnoreEnd
			}
		}
		if ($targetType instanceof TypeType) {
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof TupleType) {
				return $typeRegistry->tuple(
					array_map(
						fn(TypeInterface $type) => $typeRegistry->type($type),
						$refType->types,
					)
				);
			}
			if ($refType instanceof RecordType) {
				return $typeRegistry->record(
					array_map(
						fn(TypeInterface $type) => $typeRegistry->type($type),
						$refType->types,
					)
				);
			}
			if ($refType instanceof MetaType) {
				if (in_array($refType->value, [
					MetaTypeValue::Tuple, MetaTypeValue::Union, MetaTypeValue::Intersection
				], true)) {
					return $typeRegistry->array(
						$typeRegistry->type(
							$typeRegistry->any
						)
					);
				}
				if ($refType->value === MetaTypeValue::Record) {
					return $typeRegistry->map(
						$typeRegistry->type(
							$typeRegistry->any
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
		if ($target instanceof TypeValue) {
			$typeValue = $this->toBaseType($target->typeValue);
			if ($typeValue instanceof TupleType || $typeValue instanceof UnionType || $typeValue instanceof IntersectionType) {
				return $programRegistry->valueRegistry->tuple(
					array_map(
						fn(TypeInterface $type) => $programRegistry->valueRegistry->type($type),
						$typeValue->types
					)
				);
			}
			if ($typeValue instanceof RecordType) {
				return $programRegistry->valueRegistry->record(
					array_map(
						fn(TypeInterface $type) => $programRegistry->valueRegistry->type($type),
						$typeValue->types
					)
				);
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}