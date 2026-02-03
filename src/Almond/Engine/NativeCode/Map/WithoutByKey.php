<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class WithoutByKey implements NativeMethod {
	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(Type $targetType, Type $parameterType, Expression|null $origin): ValidationSuccess|ValidationFailure {
		$targetType = $this->toBaseType($targetType);
		$parameterType = $this->toBaseType($parameterType);
		if ($targetType instanceof RecordType) {
			if ($parameterType instanceof StringSubsetType &&
				count($parameterType->subsetValues) === 1 &&
				array_key_exists($parameterType->subsetValues[0], $targetType->types)
			) {
				$keyValue = $parameterType->subsetValues[0];
				$recordTypes = $targetType->types;
				$elementType = $recordTypes[$keyValue];
				unset($recordTypes[$keyValue]);
				return $this->validationFactory->validationSuccess(
					$this->typeRegistry->record([
						'element' => $elementType,
						'map' => $this->typeRegistry->record(
							$recordTypes,
							$targetType->restType
						)
					], null)
				);
			}
			$targetType = $targetType->asMapType();
		}
		if ($targetType instanceof MapType) {
			if ($parameterType instanceof StringType) {
				$returnType = $this->typeRegistry->record([
					'element' => $targetType->itemType,
					'map' => $this->typeRegistry->map(
						$targetType->itemType,
						$targetType->range->maxLength === PlusInfinity::value ?
							$targetType->range->minLength : max(0,
							min(
								$targetType->range->minLength - 1,
								$targetType->range->maxLength - 1
							)),
						$targetType->range->maxLength === PlusInfinity::value ?
							PlusInfinity::value : max($targetType->range->maxLength - 1, 0),
						$targetType->keyType
					)
				], null);
				return $this->validationFactory->validationSuccess(
					$this->typeRegistry->result(
						$returnType,
						$this->typeRegistry->core->mapItemNotFound
					)
				);
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
				origin: $origin
			);
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
		if ($target instanceof RecordValue) {
			if ($parameter instanceof StringValue) {
				$values = $target->values;
				if (!isset($values[$parameter->literalValue])) {
					return $this->valueRegistry->error(
						$this->valueRegistry->core->mapItemNotFound(
							$this->valueRegistry->record(['key' => $parameter])
						)
					);
				}
				$val = $values[$parameter->literalValue];
				unset($values[$parameter->literalValue]);
				return $this->valueRegistry->record([
					'element' => $val,
					'map' => $this->valueRegistry->record($values)
				]);
			}
			// @codeCoverageIgnoreStart
			throw new ExecutionException("Invalid parameter value");
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}
