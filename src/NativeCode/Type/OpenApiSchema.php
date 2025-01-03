<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Type\AliasType;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\BooleanType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\IntersectionType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\MutableType;
use Walnut\Lang\Blueprint\Type\NamedType;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\NullType;
use Walnut\Lang\Blueprint\Type\OptionalKeyType;
use Walnut\Lang\Blueprint\Type\RealSubsetType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Type\UnionType;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\IntegerSubsetType;

final readonly class OpenApiSchema implements NativeMethod {

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): Type {
		if ($targetType instanceof TypeType) {
			$refType = $targetType->refType;
			if ($refType->isSubtypeOf($this->context->typeRegistry->alias(new TypeNameIdentifier('JsonValue')))) {
				return $this->context->typeRegistry->alias(new TypeNameIdentifier('JsonValue'));
			}
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	private function typeToOpenApiSchema(Type $type): Value {
		return match(true) {
			$type instanceof AliasType && $type->name->equals(new TypeNameIdentifier('JsonValue')) =>
				$this->context->valueRegistry->record([
					'type' => $this->context->valueRegistry->string('any')
				]),
			$type instanceof NullType => $this->context->valueRegistry->record([
				'type' => $this->context->valueRegistry->string('null')
			]),
			$type instanceof BooleanType => $this->context->valueRegistry->record([
				'type' => $this->context->valueRegistry->string('boolean')
			]),
			$type instanceof RealType, $type instanceof RealSubsetType => $this->context->valueRegistry->record([
				... ['type' => $this->context->valueRegistry->string('number')],
				... (($min = $type->range->minValue) !== MinusInfinity::value ? ['minimum' => $this->context->valueRegistry->real($min)] : []),
				... (($max = $type->range->maxValue) !== PlusInfinity::value ? ['maximum' => $this->context->valueRegistry->real($max)] : []),
			]),
			$type instanceof IntegerType, $type instanceof IntegerSubsetType => $this->context->valueRegistry->record([
				... ['type' => $this->context->valueRegistry->string('integer')],
				... (($min = $type->range->minValue) !== MinusInfinity::value ? ['minimum' => $this->context->valueRegistry->real($min)] : []),
				... (($max = $type->range->maxValue) !== PlusInfinity::value ? ['maximum' => $this->context->valueRegistry->real($max)] : []),
			]),
			$type instanceof StringSubsetType => $this->context->valueRegistry->record([
				'type' => $this->context->valueRegistry->string('string'),
				'enum' => $this->context->valueRegistry->tuple(
					array_map(
						static fn(StringValue $value): StringValue => $value,
						$type->subsetValues
					)
				)
			]),
			$type instanceof StringType => $this->context->valueRegistry->record([
				... ['type' => $this->context->valueRegistry->string('string')],
				... (($min = $type->range->minLength) > 0 ? ['minLength' => $this->context->valueRegistry->integer($min)] : []),
				... (($max = $type->range->maxLength) !== PlusInfinity::value ? ['maxLength' => $this->context->valueRegistry->integer($max)] : []),
			]),
			$type instanceof NamedType => $this->context->valueRegistry->record([
				'$ref' => $this->context->valueRegistry->string(
					sprintf('#/components/schemas/%s', $type->name)
				)
			]),
			$type instanceof UnionType => $this->context->valueRegistry->record([
				'oneOf' => $this->context->valueRegistry->tuple(
					array_map(
						fn(Type $type) => $this->typeToOpenApiSchema($type),
						$type->types
					)
				)
			]),
			$type instanceof IntersectionType => $this->context->valueRegistry->record([
				'allOf' => $this->context->valueRegistry->tuple(
					array_map(
						fn(Type $type) => $this->typeToOpenApiSchema($type),
						$type->types
					)
				)
			]),
			$type instanceof ArrayType => $this->context->valueRegistry->record([
				... [
					'type' => $this->context->valueRegistry->string('array'),
					'items' => $this->typeToOpenApiSchema($type->itemType)
				],
				... (($min = $type->range->minLength) > 0 ? ['minItems' => $this->context->valueRegistry->integer($min)] : []),
				... (($max = $type->range->maxLength) !== PlusInfinity::value ? ['maxItems' => $this->context->valueRegistry->integer($max)] : []),
			]),
			$type instanceof MapType => $this->context->valueRegistry->record([
				... [
					'type' => $this->context->valueRegistry->string('object'),
					'additionalProperties' => $this->typeToOpenApiSchema($type->itemType)
				],
				... (($min = $type->range->minLength) > 0 ? ['minProperties' => $this->context->valueRegistry->integer($min)] : []),
				... (($max = $type->range->maxLength) !== PlusInfinity::value ? ['maxProperties' => $this->context->valueRegistry->integer($max)] : []),
			]),
			$type instanceof TupleType => $this->typeToOpenApiSchema($type->asArrayType()),
			$type instanceof RecordType => $this->context->valueRegistry->record([
				... [
					'type' => $this->context->valueRegistry->string('object'),
					'properties' => $this->context->valueRegistry->record(
						array_map(
							fn(Type $type) => $this->typeToOpenApiSchema($type instanceof OptionalKeyType ? $type->valueType : $type),
							$type->types
						)
					)
				],
				... (count($requiredFields = array_keys(
					array_filter($type->types, static fn(Type $type): bool => !($type instanceof OptionalKeyType))
				)) > 0 ? ['required' => $this->context->valueRegistry->tuple(
					array_map(
						fn(string $requiredField): StringValue => $this->context->valueRegistry->string($requiredField),
						$requiredFields
					)
				)] : []),
				... ($type->restType instanceof NothingType ? [] : ['additionalProperties' => $this->typeToOpenApiSchema($type->restType)])
			]),
			$type instanceof MutableType => $this->typeToOpenApiSchema($type->valueType),
			default => $this->context->valueRegistry->null
		};
	}

	public function execute(
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;

		if ($targetValue instanceof TypeValue) {
			return new TypedValue(
				$this->context->typeRegistry->alias(new TypeNameIdentifier('JsonValue')),
				$this->typeToOpenApiSchema($targetValue->typeValue)
			);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}