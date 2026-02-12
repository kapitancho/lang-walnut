<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AliasType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BooleanType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntersectionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OptionalKeyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\UnionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\CoreType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\NamedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type as TypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;

final readonly class OpenApiSchema implements NativeMethod {

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(TypeInterface $targetType, TypeInterface $parameterType, mixed $origin): ValidationSuccess|ValidationFailure {
		if ($targetType instanceof TypeType) {
			$refType = $targetType->refType;
			while($refType instanceof MutableType) {
				$refType = $refType->valueType;
			}
			if ($refType->isSubtypeOf($this->typeRegistry->core->jsonValue)) {
				return $this->validationFactory->validationSuccess(
					$this->typeRegistry->core->jsonValue
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

	private function integerToOpenApiSchema(IntegerType $type): Value {
		$result = [];
		foreach($type->numberRange->intervals as $interval) {
			/** @noinspection PhpParamsInspection */
			$result[] = $this->valueRegistry->record([
				... ['type' => $this->valueRegistry->string('integer')],
				... (($min = $interval->start) !== MinusInfinity::value ? [
					'minimum' => $this->valueRegistry->integer($min->value),
					'exclusiveMinimum' => $this->valueRegistry->boolean(!$min->inclusive)
				] : []),
				... (($max = $interval->end) !== PlusInfinity::value ? [
					'maximum' => $this->valueRegistry->integer($max->value),
					'exclusiveMaximum' => $this->valueRegistry->boolean(!$max->inclusive)
				] : []),
			]);
		}

		return count($result) > 1 ?
			$this->valueRegistry->record([
				'oneOf' => $this->valueRegistry->tuple($result)
			]) : $result[0];
	}

	private function realToOpenApiSchema(RealType $type): Value {
		$result = [];
		foreach($type->numberRange->intervals as $interval) {
			/** @noinspection PhpParamsInspection */
			$result[] = $this->valueRegistry->record([
				... ['type' => $this->valueRegistry->string('number')],
				... (($min = $interval->start) !== MinusInfinity::value ? [
					'minimum' => $this->valueRegistry->real($min->value),
					'exclusiveMinimum' => $this->valueRegistry->boolean(!$min->inclusive)
				] : []),
				... (($max = $interval->end) !== PlusInfinity::value ? [
					'maximum' => $this->valueRegistry->real($max->value),
					'exclusiveMaximum' => $this->valueRegistry->boolean(!$max->inclusive)
				] : []),
			]);
		}

		return count($result) > 1 ?
			$this->valueRegistry->record([
				'oneOf' => $this->valueRegistry->tuple($result)
			]) : $result[0];
	}

	private function typeToOpenApiSchema(TypeInterface $type): Value {
		/** @noinspection PhpParamsInspection */
		return match(true) {
			$type instanceof AliasType && $type->name->equals(CoreType::JsonValue->typeName()) =>
				$this->valueRegistry->record([
					'type' => $this->valueRegistry->string('any')
				]),
			$type instanceof NullType => $this->valueRegistry->record([
				'type' => $this->valueRegistry->string('null')
			]),
			$type instanceof BooleanType => $this->valueRegistry->record([
				'type' => $this->valueRegistry->string('boolean')
			]),
			$type instanceof RealType => $this->realToOpenApiSchema($type),
			$type instanceof IntegerType => $this->integerToOpenApiSchema($type),
			$type instanceof StringSubsetType => $this->valueRegistry->record([
				'type' => $this->valueRegistry->string('string'),
				'enum' => $this->valueRegistry->tuple(
					array_map(
						fn(string $value): StringValue => $this->valueRegistry->string($value),
						$type->subsetValues
					)
				)
			]),
			$type instanceof StringType => $this->valueRegistry->record([
				... ['type' => $this->valueRegistry->string('string')],
				... (($min = $type->range->minLength) > 0 ? ['minLength' => $this->valueRegistry->integer($min)] : []),
				... (($max = $type->range->maxLength) !== PlusInfinity::value ? ['maxLength' => $this->valueRegistry->integer($max)] : []),
			]),
			$type instanceof NamedType => $this->valueRegistry->record([
				'$ref' => $this->valueRegistry->string(
					sprintf('#/components/schemas/%s', $type->name)
				)
			]),
			$type instanceof UnionType => $this->valueRegistry->record([
				'oneOf' => $this->valueRegistry->tuple(
					array_map(
						fn(TypeInterface $type) => $this->typeToOpenApiSchema($type),
						$type->types
					)
				)
			]),
			$type instanceof IntersectionType => $this->valueRegistry->record([
				'allOf' => $this->valueRegistry->tuple(
					array_map(
						fn(TypeInterface $type) => $this->typeToOpenApiSchema($type),
						$type->types
					)
				)
			]),
			$type instanceof ArrayType => $this->valueRegistry->record([
				... [
					'type' => $this->valueRegistry->string('array'),
					'items' => $this->typeToOpenApiSchema($type->itemType)
				],
				... (($min = $type->range->minLength) > 0 ? ['minItems' => $this->valueRegistry->integer($min)] : []),
				... (($max = $type->range->maxLength) !== PlusInfinity::value ? ['maxItems' => $this->valueRegistry->integer($max)] : []),
			]),
			$type instanceof SetType => $this->valueRegistry->record([
				... [
					'type' => $this->valueRegistry->string('array'),
					'items' => $this->typeToOpenApiSchema($type->itemType),
					'uniqueItems' => $this->valueRegistry->boolean(true)
				],
				... (($min = $type->range->minLength) > 0 ? ['minItems' => $this->valueRegistry->integer($min)] : []),
				... (($max = $type->range->maxLength) !== PlusInfinity::value ? ['maxItems' => $this->valueRegistry->integer($max)] : []),
			]),
			$type instanceof MapType => $this->valueRegistry->record([
				... [
					'type' => $this->valueRegistry->string('object'),
					'additionalProperties' => $this->typeToOpenApiSchema($type->itemType)
				],
				... (($min = $type->range->minLength) > 0 ? ['minProperties' => $this->valueRegistry->integer($min)] : []),
				... (($max = $type->range->maxLength) !== PlusInfinity::value ? ['maxProperties' => $this->valueRegistry->integer($max)] : []),
			]),
			$type instanceof TupleType => $this->typeToOpenApiSchema($type->asArrayType()),
			$type instanceof RecordType => $this->valueRegistry->record([
				... [
					'type' => $this->valueRegistry->string('object'),
					'properties' => $this->valueRegistry->record(
						array_map(
							fn(TypeInterface $type) => $this->typeToOpenApiSchema($type instanceof OptionalKeyType ? $type->valueType : $type),
							$type->types
						)
					)
				],
				... (count($requiredFields = array_keys(
					array_filter($type->types, static fn(TypeInterface $type): bool => !($type instanceof OptionalKeyType))
				)) > 0 ? ['required' => $this->valueRegistry->tuple(
					array_map(
						fn(string $requiredField): StringValue => $this->valueRegistry->string($requiredField),
						$requiredFields
					)
				)] : []),
				... ($type->restType instanceof NothingType ? [] : ['additionalProperties' => $this->typeToOpenApiSchema($type->restType)])
			]),
			$type instanceof MutableType => $this->typeToOpenApiSchema($type->valueType),
			default => $this->valueRegistry->null
		};
	}

	public function execute(Value $target, Value $parameter): Value {
		if ($target instanceof TypeValue) {
			return $this->typeToOpenApiSchema($target->typeValue);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}
}
