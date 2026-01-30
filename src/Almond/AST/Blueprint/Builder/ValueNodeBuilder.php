<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Builder;

use BcMath\Number;
use Walnut\Lang\Almond\AST\Blueprint\Node\FunctionBodyNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\EnumerationValueNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\TypeNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\NameAndTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\AtomValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\BytesValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\DataValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\EnumerationValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\ErrorValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\FalseValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\FunctionValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\IntegerValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\MutableValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\NullValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\RealValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\RecordValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\SetValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\StringValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\TrueValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\TupleValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\TypeValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\ValueNode;

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
	public function atomValue(TypeNameNode $name): AtomValueNode;
	public function dataValue(TypeNameNode $name, ValueNode $value): DataValueNode;
	public function enumerationValue(TypeNameNode $name, EnumerationValueNameNode $enumValue): EnumerationValueNode;
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