<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
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
use Walnut\Lang\Blueprint\Type\SetType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Type\UnionType;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Implementation\Type\IntegerSubsetType;

final readonly class OpenApiSchema implements NativeMethod {

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
		if ($targetType instanceof TypeType) {
			$refType = $targetType->refType;
			if ($refType->isSubtypeOf($programRegistry->typeRegistry->alias(new TypeNameIdentifier('JsonValue')))) {
				return $programRegistry->typeRegistry->alias(new TypeNameIdentifier('JsonValue'));
			}
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	private function typeToOpenApiSchema(ProgramRegistry $programRegistry, Type $type): Value {
		/** @noinspection PhpParamsInspection */
		return match(true) {
			$type instanceof AliasType && $type->name->equals(new TypeNameIdentifier('JsonValue')) =>
				$programRegistry->valueRegistry->record([
					'type' => $programRegistry->valueRegistry->string('any')
				]),
			$type instanceof NullType => $programRegistry->valueRegistry->record([
				'type' => $programRegistry->valueRegistry->string('null')
			]),
			$type instanceof BooleanType => $programRegistry->valueRegistry->record([
				'type' => $programRegistry->valueRegistry->string('boolean')
			]),
			$type instanceof RealType, $type instanceof RealSubsetType => $programRegistry->valueRegistry->record([
				... ['type' => $programRegistry->valueRegistry->string('number')],
				... (($min = $type->numberRange->min) !== MinusInfinity::value ? [
					'minimum' => $programRegistry->valueRegistry->real($min->value),
					'exclusiveMinimum' => $programRegistry->valueRegistry->boolean(!$min->inclusive)
				] : []),
				... (($max = $type->numberRange->max) !== PlusInfinity::value ? [
					'maximum' => $programRegistry->valueRegistry->real($max->value),
					'exclusiveMaximum' => $programRegistry->valueRegistry->boolean(!$max->inclusive)
				] : []),
			]),
			$type instanceof IntegerType, $type instanceof IntegerSubsetType => $programRegistry->valueRegistry->record([
				... ['type' => $programRegistry->valueRegistry->string('integer')],
				... (($min = $type->numberRange->min) !== MinusInfinity::value ? [
					'minimum' => $programRegistry->valueRegistry->real($min->value),
					'exclusiveMinimum' => $programRegistry->valueRegistry->boolean(!$min->inclusive)
				] : []),
				... (($max = $type->numberRange->max) !== PlusInfinity::value ? [
					'maximum' => $programRegistry->valueRegistry->real($max->value),
					'exclusiveMaximum' => $programRegistry->valueRegistry->boolean(!$max->inclusive)
				] : []),
			]),
			$type instanceof StringSubsetType => $programRegistry->valueRegistry->record([
				'type' => $programRegistry->valueRegistry->string('string'),
				'enum' => $programRegistry->valueRegistry->tuple(
					array_map(
						static fn(string $value): StringValue => $programRegistry->valueRegistry->string($value),
						$type->subsetValues
					)
				)
			]),
			$type instanceof StringType => $programRegistry->valueRegistry->record([
				... ['type' => $programRegistry->valueRegistry->string('string')],
				... (($min = $type->range->minLength) > 0 ? ['minLength' => $programRegistry->valueRegistry->integer($min)] : []),
				... (($max = $type->range->maxLength) !== PlusInfinity::value ? ['maxLength' => $programRegistry->valueRegistry->integer($max)] : []),
			]),
			$type instanceof NamedType => $programRegistry->valueRegistry->record([
				'$ref' => $programRegistry->valueRegistry->string(
					sprintf('#/components/schemas/%s', $type->name)
				)
			]),
			$type instanceof UnionType => $programRegistry->valueRegistry->record([
				'oneOf' => $programRegistry->valueRegistry->tuple(
					array_map(
						fn(Type $type) => $this->typeToOpenApiSchema($programRegistry, $type),
						$type->types
					)
				)
			]),
			$type instanceof IntersectionType => $programRegistry->valueRegistry->record([
				'allOf' => $programRegistry->valueRegistry->tuple(
					array_map(
						fn(Type $type) => $this->typeToOpenApiSchema($programRegistry, $type),
						$type->types
					)
				)
			]),
			$type instanceof ArrayType => $programRegistry->valueRegistry->record([
				... [
					'type' => $programRegistry->valueRegistry->string('array'),
					'items' => $this->typeToOpenApiSchema($programRegistry, $type->itemType)
				],
				... (($min = $type->range->minLength) > 0 ? ['minItems' => $programRegistry->valueRegistry->integer($min)] : []),
				... (($max = $type->range->maxLength) !== PlusInfinity::value ? ['maxItems' => $programRegistry->valueRegistry->integer($max)] : []),
			]),
			$type instanceof SetType => $programRegistry->valueRegistry->record([
				... [
					'type' => $programRegistry->valueRegistry->string('array'),
					'items' => $this->typeToOpenApiSchema($programRegistry, $type->itemType),
					'uniqueItems' => $programRegistry->valueRegistry->boolean(true)
				],
				... (($min = $type->range->minLength) > 0 ? ['minItems' => $programRegistry->valueRegistry->integer($min)] : []),
				... (($max = $type->range->maxLength) !== PlusInfinity::value ? ['maxItems' => $programRegistry->valueRegistry->integer($max)] : []),
			]),
			$type instanceof MapType => $programRegistry->valueRegistry->record([
				... [
					'type' => $programRegistry->valueRegistry->string('object'),
					'additionalProperties' => $this->typeToOpenApiSchema($programRegistry, $type->itemType)
				],
				... (($min = $type->range->minLength) > 0 ? ['minProperties' => $programRegistry->valueRegistry->integer($min)] : []),
				... (($max = $type->range->maxLength) !== PlusInfinity::value ? ['maxProperties' => $programRegistry->valueRegistry->integer($max)] : []),
			]),
			$type instanceof TupleType => $this->typeToOpenApiSchema($programRegistry, $type->asArrayType()),
			$type instanceof RecordType => $programRegistry->valueRegistry->record([
				... [
					'type' => $programRegistry->valueRegistry->string('object'),
					'properties' => $programRegistry->valueRegistry->record(
						array_map(
							fn(Type $type) => $this->typeToOpenApiSchema($programRegistry, $type instanceof OptionalKeyType ? $type->valueType : $type),
							$type->types
						)
					)
				],
				... (count($requiredFields = array_keys(
					array_filter($type->types, static fn(Type $type): bool => !($type instanceof OptionalKeyType))
				)) > 0 ? ['required' => $programRegistry->valueRegistry->tuple(
					array_map(
						fn(string $requiredField): StringValue => $programRegistry->valueRegistry->string($requiredField),
						$requiredFields
					)
				)] : []),
				... ($type->restType instanceof NothingType ? [] : ['additionalProperties' => $this->typeToOpenApiSchema($programRegistry, $type->restType)])
			]),
			$type instanceof MutableType => $this->typeToOpenApiSchema($programRegistry, $type->valueType),
			default => $programRegistry->valueRegistry->null
		};
	}

	public function execute(
		ProgramRegistry        $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		$targetValue = $target;

		if ($targetValue instanceof TypeValue) {
			return (
				$this->typeToOpenApiSchema($programRegistry, $targetValue->typeValue)
			);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}