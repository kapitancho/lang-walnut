<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntersectionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MetaType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\UnionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\MetaTypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type as TypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class ItemTypes implements NativeMethod {

	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(TypeInterface $targetType, TypeInterface $parameterType, Expression|null $origin): ValidationSuccess|ValidationFailure {
		if ($targetType instanceof IntersectionType) {
			foreach($targetType->types as $type) {
				$result = $this->validate($type, $parameterType, $origin);
				if ($result instanceof ValidationSuccess) {
					return $result;
				}
			}
		}
		if ($targetType instanceof TypeType) {
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof TupleType) {
				return $this->validationFactory->validationSuccess(
					$this->typeRegistry->tuple(
						array_map(
							fn(TypeInterface $type) => $this->typeRegistry->type($type),
							$refType->types,
						),
						null
					)
				);
			}
			if ($refType instanceof RecordType) {
				return $this->validationFactory->validationSuccess(
					$this->typeRegistry->record(
						array_map(
							fn(TypeInterface $type) => $this->typeRegistry->type($type),
							$refType->types,
						),
						null
					)
				);
			}
			if ($refType instanceof MetaType) {
				if (in_array($refType->value, [
					MetaTypeValue::Tuple, MetaTypeValue::Union, MetaTypeValue::Intersection
				], true)) {
					return $this->validationFactory->validationSuccess(
						$this->typeRegistry->array(
							$this->typeRegistry->type(
								$this->typeRegistry->any
							)
						)
					);
				}
				if ($refType->value === MetaTypeValue::Record) {
					return $this->validationFactory->validationSuccess(
						$this->typeRegistry->map(
							$this->typeRegistry->type(
								$this->typeRegistry->any,
							),
							0,
							PlusInfinity::value,
							$this->typeRegistry->string()
						)
					);
				}
			}
		}
		// @codeCoverageIgnoreStart
		return $this->validationFactory->error(
			ValidationErrorType::invalidTargetType,
			sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType),
			origin: $origin
		);
		// @codeCoverageIgnoreEnd
	}

	public function execute(Value $target, Value $parameter): Value {
		if ($target instanceof TypeValue) {
			$typeValue = $this->toBaseType($target->typeValue);
			if ($typeValue instanceof TupleType || $typeValue instanceof UnionType || $typeValue instanceof IntersectionType) {
				return $this->valueRegistry->tuple(
					array_map(
						fn(TypeInterface $type) => $this->valueRegistry->type($type),
						$typeValue->types
					)
				);
			}
			if ($typeValue instanceof RecordType) {
				return $this->valueRegistry->record(
					array_map(
						fn(TypeInterface $type) => $this->valueRegistry->type($type),
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
