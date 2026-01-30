<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Registry;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Error\IncompatibleValueType;
use Walnut\Lang\Almond\Engine\Blueprint\Error\InvalidArgument;
use Walnut\Lang\Almond\Engine\Blueprint\Error\UnknownEnumerationValue;
use Walnut\Lang\Almond\Engine\Blueprint\Error\UnknownType;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Value\AtomValue;
use Walnut\Lang\Almond\Engine\Blueprint\Value\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Value\BytesValue;
use Walnut\Lang\Almond\Engine\Blueprint\Value\DataValue;
use Walnut\Lang\Almond\Engine\Blueprint\Value\EnumerationValue;
use Walnut\Lang\Almond\Engine\Blueprint\Value\ErrorValue as ErrorValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Value\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Value\MutableValue;
use Walnut\Lang\Almond\Engine\Blueprint\Value\OpenValue;
use Walnut\Lang\Almond\Engine\Blueprint\Value\RealValue;
use Walnut\Lang\Almond\Engine\Blueprint\Value\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Value\SetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Value\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Value\SealedValue;
use Walnut\Lang\Almond\Engine\Blueprint\Value\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Value\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

interface ValueRegistry {
	public Value $null { get; }
	public Value $true { get; }
	public Value $false { get; }
	public function type(Type $refType): TypeValue;

	public function boolean(bool $value): BooleanValue;
	public function error(Value $value): ErrorValueInterface;
	public function mutable(Type $type, Value $value): MutableValue;

	/**
	 * @param list<Value> $values
	 * @throws InvalidArgument
	 */
	public function tuple(array $values): TupleValue;
	/**
	 * @param array<string, Value> $values
	 * @throws InvalidArgument
	 */
	public function record(array $values): RecordValue;
	/**
	 * @param list<Value> $values
	 * @throws InvalidArgument
	 */
	public function set(array $values): SetValue;

	/** @throws UnknownType */
	public function atom(TypeName $typeName): AtomValue;
	/** @throws UnknownType|UnknownEnumerationValue */
	public function enumeration(TypeName $typeName, EnumerationValueName $valueName): EnumerationValue;
	/** @throws UnknownType */
	public function data(TypeName $typeName, Value $value): DataValue;
	/** @throws UnknownType|IncompatibleValueType */
	public function open(TypeName $typeName, Value $value): OpenValue;
	/** @throws UnknownType|IncompatibleValueType */
	public function sealed(TypeName $typeName, Value $value): SealedValue;

	public function integer(Number|int $value): IntegerValue;
	public function real(Number|float $value): RealValue;
	public function string(string $value): StringValue;
	public function bytes(string $value): BytesValue;
}