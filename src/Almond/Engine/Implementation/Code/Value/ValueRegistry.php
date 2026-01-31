<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Value;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\InvalidArgument;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\UnknownEnumerationValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\UnknownType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry as TypeRegistryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\AtomValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\DataValue as DataValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\EnumerationValue as EnumerationValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue as ErrorValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\MutableValue as MutableValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\OpenValue as OpenValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SealedValue as SealedValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue as TupleValueInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\IncompatibleValueType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry as ValueRegistryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Common\EscapeCharHandler;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Implementation\Code\Value\BuiltIn\BytesValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\Value\BuiltIn\DataValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\Value\BuiltIn\MutableValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\Value\BuiltIn\OpenValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\Value\BuiltIn\SealedValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\Value\BuiltIn\SetValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\Value\BuiltIn\TypeValue;

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