<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Value;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\InvalidArgument;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\UnknownEnumerationValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\UnknownType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\AtomValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BytesValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\DataValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\EnumerationValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue as ErrorValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\MutableValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\OpenValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SealedValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;

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

	public ValueRegistryCore $core { get; }
}