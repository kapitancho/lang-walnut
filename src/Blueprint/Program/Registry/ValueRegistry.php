<?php

namespace Walnut\Lang\Blueprint\Program\Registry;

use bcmath\Number;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Blueprint\Program\UnknownType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\UnknownEnumerationValue;
use Walnut\Lang\Blueprint\Value\AtomValue;
use Walnut\Lang\Blueprint\Value\BooleanValue;
use Walnut\Lang\Blueprint\Value\DataValue;
use Walnut\Lang\Blueprint\Value\EnumerationValue;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\MutableValue;
use Walnut\Lang\Blueprint\Value\NullValue;
use Walnut\Lang\Blueprint\Value\OpenValue;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\SealedValue;
use Walnut\Lang\Blueprint\Value\SetValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Blueprint\Value\Value;

interface ValueRegistry {
	public NullValue $null { get; }
	public BooleanValue $true { get; }
	public BooleanValue $false { get; }

	public function boolean(bool $value): BooleanValue;
	public function integer(Number|int $value): IntegerValue;
	public function real(Number|float $value): RealValue;
	public function string(string $value): StringValue;

	/** @param list<Value> $values */
	public function tuple(array $values): TupleValue;
	/** @param array<string, Value> $values */
	public function record(array $values): RecordValue;
	/** @param list<Value> $values */
	public function set(array $values): SetValue;

	public function function(
		Type $parameterType,
		VariableNameIdentifier|null $parameterName,
		Type $dependencyType,
		Type $returnType,
		FunctionBody $body,
		string $functionName = '(Unknown)'
	): FunctionValue;

	public function mutable(Type $type, Value $value): MutableValue;
	public function type(Type $type): TypeValue;
	public function error(Value $value): ErrorValue;

	/** @throws UnknownType */
	public function atom(TypeNameIdentifier $typeName): AtomValue;

	/** @throws UnknownType */
	/** @throws UnknownEnumerationValue */
	public function enumerationValue(
		TypeNameIdentifier $typeName,
		EnumValueIdentifier $valueIdentifier
	): EnumerationValue;

	/** @throws UnknownType */
	public function dataValue(
		TypeNameIdentifier $typeName,
		Value $value
	): DataValue;

	/** @throws UnknownType */
	public function openValue(
		TypeNameIdentifier $typeName,
		Value $value
	): OpenValue;

	/** @throws UnknownType */
	public function sealedValue(
		TypeNameIdentifier $typeName,
		Value $value
	): SealedValue;
}