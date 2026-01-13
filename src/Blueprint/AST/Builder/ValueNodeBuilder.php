<?php

namespace Walnut\Lang\Blueprint\AST\Builder;

use BcMath\Number;
use Walnut\Lang\Blueprint\AST\Node\FunctionBodyNode;
use Walnut\Lang\Blueprint\AST\Node\NameAndTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\AST\Node\Value\AtomValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\BytesValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\DataValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\EnumerationValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\ErrorValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\FalseValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\FunctionValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\IntegerValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\MutableValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\NullValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\RealValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\RecordValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\SetValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\StringValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\TrueValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\TupleValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\TypeValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\ValueNode;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;

interface ValueNodeBuilder {
	public NullValueNode $nullValue { get; }
	public TrueValueNode $trueValue { get; }
	public FalseValueNode $falseValue { get; }
	public function integerValue(Number $value): IntegerValueNode;
	public function realValue(Number $value): RealValueNode;
	public function stringValue(string $value): StringValueNode;
	public function bytesValue(string $value): BytesValueNode;
	public function typeValue(TypeNode $type): TypeValueNode;
	public function errorValue(ValueNode $value): ErrorValueNode;
	public function mutableValue(TypeNode $type, ValueNode $value): MutableValueNode;
	public function atomValue(TypeNameIdentifier $name): AtomValueNode;
	public function dataValue(TypeNameIdentifier $name, ValueNode $value): DataValueNode;
	public function enumerationValue(TypeNameIdentifier $name, EnumValueIdentifier $enumValue): EnumerationValueNode;
	/** @param array<string, ValueNode> $values */
	public function recordValue(array $values): RecordValueNode;
	/** @param list<ValueNode> $values */
	public function tupleValue(array $values): TupleValueNode;
	/** @param list<ValueNode> $values */
	public function setValue(array $values): SetValueNode;
	public function functionValue(
		NameAndTypeNode $parameter,
		NameAndTypeNode $dependency,
		TypeNode $returnType,
		FunctionBodyNode $functionBody,
	): FunctionValueNode;
}