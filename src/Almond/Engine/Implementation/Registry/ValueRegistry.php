<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Registry;

use BcMath\Number;
use Walnut\Lang\Almond\AST\Blueprint\Parser\EscapeCharHandler;
use Walnut\Lang\Almond\Engine\Blueprint\Error\IncompatibleValueType;
use Walnut\Lang\Almond\Engine\Blueprint\Error\InvalidArgument;
use Walnut\Lang\Almond\Engine\Blueprint\Error\UnknownEnumerationValue;
use Walnut\Lang\Almond\Engine\Blueprint\Error\UnknownType;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\TypeRegistry as TypeRegistryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\ValueRegistry as ValueRegistryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Value\AtomValue;
use Walnut\Lang\Almond\Engine\Blueprint\Value\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Value\DataValue as DataValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Value\EnumerationValue as EnumerationValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Value\ErrorValue as ErrorValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Value\MutableValue as MutableValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Value\OpenValue as OpenValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Value\SealedValue as SealedValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Value\TupleValue as TupleValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Value\P\BytesValue;
use Walnut\Lang\Almond\Engine\Implementation\Value\P\DataValue;
use Walnut\Lang\Almond\Engine\Implementation\Value\P\ErrorValue;
use Walnut\Lang\Almond\Engine\Implementation\Value\P\IntegerValue;
use Walnut\Lang\Almond\Engine\Implementation\Value\P\MutableValue;
use Walnut\Lang\Almond\Engine\Implementation\Value\P\OpenValue;
use Walnut\Lang\Almond\Engine\Implementation\Value\P\RealValue;
use Walnut\Lang\Almond\Engine\Implementation\Value\P\RecordValue;
use Walnut\Lang\Almond\Engine\Implementation\Value\P\SealedValue;
use Walnut\Lang\Almond\Engine\Implementation\Value\P\SetValue;
use Walnut\Lang\Almond\Engine\Implementation\Value\P\StringValue;
use Walnut\Lang\Almond\Engine\Implementation\Value\P\TupleValue;
use Walnut\Lang\Almond\Engine\Implementation\Value\P\TypeValue;

final readonly class ValueRegistry implements ValueRegistryInterface {

	public Value $null;
	public Value $true;
	public Value $false;

	public ValueRegistryCore $core;


	public function __construct(
		private TypeRegistryInterface   $typeRegistry,
		private EscapeCharHandler       $stringEscapeCharHandler,
		private EscapeCharHandler       $bytesEscapeCharHandler,
	) {
		$this->null = $this->typeRegistry->null->value;
		$this->true = $this->typeRegistry->true->value;
		$this->false = $this->typeRegistry->false->value;

		$this->core = new ValueRegistryCore($this);
	}

	public function type(Type $refType): TypeValue {
		return new TypeValue($this->typeRegistry, $refType);
	}

	public function boolean(bool $value): BooleanValue {
		return $value ? $this->true : $this->false;
	}

	public function error(Value $value): ErrorValueInterface {
		return new ErrorValue(
			$this->typeRegistry, $value
		);
	}

	public function mutable(Type $type, Value $value): MutableValueInterface {
		return new MutableValue(
			$this->typeRegistry, $type, $value
		);
	}

	/**
	 * @param list<Value> $values
	 * @throws InvalidArgument
	 */
	public function tuple(array $values): TupleValueInterface {
		return new TupleValue(
			$this->typeRegistry,
			$values
		);
	}

	/**
	 * @param array<string, Value> $values
	 * @throws InvalidArgument
	 */
	public function record(array $values): RecordValue {
		return new RecordValue(
			$this->typeRegistry,
			$values
		);
	}

	/**
	 * @param list<Value> $values
	 * @throws InvalidArgument
	 */
	public function set(array $values): SetValue {
		return new SetValue(
			$this->typeRegistry,
			$values
		);
	}

	/** @throws UnknownType */
	public function atom(TypeName $typeName): AtomValue {
		return $this->typeRegistry->userland->atom($typeName)->value;
	}

	/** @throws UnknownType|UnknownEnumerationValue */
	public function enumeration(TypeName $typeName, EnumerationValueName $valueName): EnumerationValueInterface {
		return $this->typeRegistry->userland->enumeration($typeName)
			->value($valueName);
	}

	/** @throws UnknownType */
	public function data(TypeName $typeName, Value $value): DataValueInterface {
		return new DataValue(
			$this->typeRegistry->userland->data($typeName),
			$value
		);
	}

	/** @throws UnknownType|IncompatibleValueType */
	public function open(TypeName $typeName, Value $value): OpenValueInterface {
		return new OpenValue(
			$this->typeRegistry->userland->open($typeName),
			$value
		);
	}

	/** @throws UnknownType|IncompatibleValueType */
	public function sealed(TypeName $typeName, Value $value): SealedValueInterface {
		return new SealedValue(
			$this->typeRegistry->userland->sealed($typeName),
			$value
		);
	}

	public function integer(Number|int $value): IntegerValue {
		return new IntegerValue(
			$this->typeRegistry,
			is_int($value) ? new Number($value) : $value
		);
	}
	public function real(Number|float $value): RealValue {
		return new RealValue(
			$this->typeRegistry,
			is_float($value) ? new Number((string)$value) : $value
		);
	}

	public function string(string $value): StringValue {
		return new StringValue(
			$this->typeRegistry,
			$this->stringEscapeCharHandler,
			$value
		);
	}
	public function bytes(string $value): BytesValue {
		return new BytesValue(
			$this->typeRegistry,
			$this->bytesEscapeCharHandler,
			$value
		);
	}

}