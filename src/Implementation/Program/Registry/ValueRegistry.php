<?php

namespace Walnut\Lang\Implementation\Program\Registry;

use BcMath\Number;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Blueprint\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\DependencyContainer\DependencyContainer;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry as ValueRegistryInterface;
use Walnut\Lang\Blueprint\Program\UnknownEnumerationValue;
use Walnut\Lang\Blueprint\Program\UnknownType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\AtomValue as AtomValueInterface;
use Walnut\Lang\Blueprint\Value\BooleanValue;
use Walnut\Lang\Blueprint\Value\EnumerationValue as EnumerationValueInterface;
use Walnut\Lang\Blueprint\Value\NullValue;
use Walnut\Lang\Blueprint\Value\RecordValue as RecordValueInterface;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Value\ErrorValue;
use Walnut\Lang\Implementation\Value\FunctionValue;
use Walnut\Lang\Implementation\Value\IntegerValue;
use Walnut\Lang\Implementation\Value\MutableValue;
use Walnut\Lang\Implementation\Value\RealValue;
use Walnut\Lang\Implementation\Value\RecordValue;
use Walnut\Lang\Implementation\Value\SealedValue;
use Walnut\Lang\Implementation\Value\StringValue;
use Walnut\Lang\Implementation\Value\SubtypeValue;
use Walnut\Lang\Implementation\Value\TupleValue;
use Walnut\Lang\Implementation\Value\TypeValue;

final class ValueRegistry implements ValueRegistryInterface {
	public function __construct(
		private readonly TypeRegistry $typeRegistry,
		private readonly DependencyContainer $dependencyContainer
	) {}

	public NullValue $null {
		get => $this->typeRegistry->null->value;
	}

    public function boolean(bool $value): BooleanValue {
		return $value ? $this->true : $this->false;
	}

	public BooleanValue $true {
		get => $this->typeRegistry->true->value;
	}

	public BooleanValue $false {
		get => $this->typeRegistry->false->value;
	}

    public function integer(Number $value): IntegerValue {
		return new IntegerValue($this->typeRegistry,$value);
	}

    public function real(Number $value): RealValue {
	    return new RealValue($this->typeRegistry, $value);
	}

    public function string(string $value): StringValue {
	    return new StringValue($this->typeRegistry, $value);
	}

	/** @param list<Value> $values */
	public function tuple(array $values): TupleValue {
		return new TupleValue(
			$this->typeRegistry,
			$values
		);
	}

	/** @param array<string, Value> $values */
	public function record(array $values): RecordValue {
		return new RecordValue(
			$this->typeRegistry,
			$values
		);
	}

	public function mutable(Type $type, Value $value): MutableValue {
		return new MutableValue(
			$this->typeRegistry,
			$type,
			$value
		);
	}

	public function function(
		Type $parameterType,
		Type $dependencyType,
		Type $returnType,
		FunctionBody $body
    ): FunctionValue {
		return new FunctionValue(
			$this->typeRegistry,
			$this,
			$this->dependencyContainer,
			$parameterType,
			$dependencyType,
			$returnType,
			$body,
			null,
			null
		);
    }

    public function type(Type $type): TypeValue {
		return new TypeValue($this->typeRegistry, $type);
	}

    public function error(Value $value): ErrorValue {
        return new ErrorValue($this->typeRegistry, $value);
    }

    /** @throws UnknownType */
    public function atom(TypeNameIdentifier $typeName): AtomValueInterface {
		return $this->typeRegistry->atom($typeName)->value;
	}

    /** @throws UnknownType */
    /** @throws UnknownEnumerationValue */
    public function enumerationValue(
        TypeNameIdentifier $typeName,
        EnumValueIdentifier $valueIdentifier
    ): EnumerationValueInterface {
		return $this->typeRegistry->enumeration($typeName)
			->value($valueIdentifier);
	}

	/** @throws UnknownType */
    public function subtypeValue(
        TypeNameIdentifier $typeName,
        Value $baseValue
    ): SubtypeValue {
		return new SubtypeValue(
			$this->typeRegistry,
			$typeName,
			$baseValue
		);
	}

	/** @throws UnknownType */
    public function sealedValue(
        TypeNameIdentifier $typeName,
        RecordValueInterface $value
    ): SealedValue {
		return new SealedValue(
			$this->typeRegistry,
			$typeName,
			$value
		);
	}
}