<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\EnumerationSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MetaType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\MetaTypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type as TypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class Values implements NativeMethod {

	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(TypeInterface $targetType, TypeInterface $parameterType, Expression|null $origin): ValidationSuccess|ValidationFailure {
		if ($parameterType instanceof NullType) {
			if ($targetType instanceof TypeType) {
				$refType = $this->toBaseType($targetType->refType);
				if ($refType instanceof MetaType) {
					$t = match($refType->value) {
						MetaTypeValue::Enumeration,
						MetaTypeValue::EnumerationSubset
							=> $this->typeRegistry->metaType(MetaTypeValue::Enumeration),
						MetaTypeValue::IntegerSubset => $this->typeRegistry->integer(),
						MetaTypeValue::RealSubset => $this->typeRegistry->real(),
						MetaTypeValue::StringSubset => $this->typeRegistry->string(),
						default => null
					};
					if ($t) {
						return $this->validationFactory->validationSuccess(
							$this->typeRegistry->array($t, 1)
						);
					}
				}
				if ($refType instanceof IntegerSubsetType ||
					$refType instanceof RealSubsetType ||
					$refType instanceof StringSubsetType ||
					$refType instanceof EnumerationSubsetType
				) {
					$l = count($refType->subsetValues);
					return $this->validationFactory->validationSuccess(
						$this->typeRegistry->array($refType, $l, $l)
					);
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
		return $this->validationFactory->error(
			ValidationErrorType::invalidParameterType,
			sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
			origin: $origin
		);
	}

	public function execute(Value $target, Value $parameter): Value {
		if ($parameter instanceof NullValue) {
			if ($target instanceof TypeValue) {
				$refType = $this->toBaseType($target->typeValue);
				if ($refType instanceof EnumerationSubsetType) {
					return $this->valueRegistry->tuple(
						array_values(
							array_unique(
								$refType->subsetValues
							)
						)
					);
				}
				if ($refType instanceof IntegerSubsetType) {
					return $this->valueRegistry->tuple(
						array_map(
							fn(Number $value): IntegerValue => $this->valueRegistry->integer($value),
							array_values(
								array_unique(
									$refType->subsetValues
								)
							)
						)
					);
				}
				if ($refType instanceof RealSubsetType) {
					return $this->valueRegistry->tuple(
						array_map(
							fn(Number $value): RealValue => $this->valueRegistry->real($value),
							array_values(
								array_unique(
									$refType->subsetValues
								)
							)
						)
					);
				}
				if ($refType instanceof StringSubsetType) {
					return $this->valueRegistry->tuple(
						array_map(
							fn(string $value): StringValue => $this->valueRegistry->string($value),
							array_values(
								array_unique(
									$refType->subsetValues
								)
							)
						)
					);
				}
			}
			// @codeCoverageIgnoreStart
			throw new ExecutionException("Invalid target value");
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}
}
