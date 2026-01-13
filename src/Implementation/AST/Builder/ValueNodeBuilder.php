<?php

namespace Walnut\Lang\Implementation\AST\Builder;

use BcMath\Number;
use Walnut\Lang\Blueprint\AST\Builder\ValueNodeBuilder as ValueNodeBuilderInterface;
use Walnut\Lang\Blueprint\AST\Builder\SourceLocator;
use Walnut\Lang\Blueprint\AST\Node\FunctionBodyNode as FunctionBodyNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\NameAndTypeNode as NameAndTypeNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation as SourceLocationInterface;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\AST\Node\Value\AtomValueNode as AtomValueNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Value\ValueNode;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Implementation\AST\Node\Value\AtomValueNode;
use Walnut\Lang\Implementation\AST\Node\Value\BytesValueNode;
use Walnut\Lang\Implementation\AST\Node\Value\DataValueNode;
use Walnut\Lang\Implementation\AST\Node\Value\EnumerationValueNode;
use Walnut\Lang\Implementation\AST\Node\Value\ErrorValueNode;
use Walnut\Lang\Implementation\AST\Node\Value\FalseValueNode;
use Walnut\Lang\Implementation\AST\Node\Value\FunctionValueNode;
use Walnut\Lang\Implementation\AST\Node\Value\IntegerValueNode;
use Walnut\Lang\Implementation\AST\Node\Value\MutableValueNode;
use Walnut\Lang\Implementation\AST\Node\Value\NullValueNode;
use Walnut\Lang\Implementation\AST\Node\Value\RealValueNode;
use Walnut\Lang\Implementation\AST\Node\Value\RecordValueNode;
use Walnut\Lang\Implementation\AST\Node\Value\SetValueNode;
use Walnut\Lang\Implementation\AST\Node\Value\StringValueNode;
use Walnut\Lang\Implementation\AST\Node\Value\TrueValueNode;
use Walnut\Lang\Implementation\AST\Node\Value\TupleValueNode;
use Walnut\Lang\Implementation\AST\Node\Value\TypeValueNode;

final class ValueNodeBuilder implements ValueNodeBuilderInterface {

	public function __construct(private readonly SourceLocator $sourceLocator) {}

	private function getSourceLocation(): SourceLocationInterface {
		return $this->sourceLocator->getSourceLocation();
	}

	public NullValueNode $nullValue {
		get {
			return new NullValueNode($this->getSourceLocation());
		}
	}
	public TrueValueNode $trueValue {
		get {
			return new TrueValueNode($this->getSourceLocation());
		}
	}
	public FalseValueNode $falseValue {
		get {
			return new FalseValueNode($this->getSourceLocation());
		}
	}

	public function integerValue(Number $value): IntegerValueNode {
		return new IntegerValueNode($this->getSourceLocation(), $value);
	}

	public function realValue(Number $value): RealValueNode {
		return new RealValueNode($this->getSourceLocation(), $value);
	}

	public function stringValue(string $value): StringValueNode {
		return new StringValueNode($this->getSourceLocation(), $value);
	}

	public function bytesValue(string $value): BytesValueNode {
		return new BytesValueNode($this->getSourceLocation(), $value);
	}

	public function typeValue(TypeNode $type): TypeValueNode {
		return new TypeValueNode($this->getSourceLocation(), $type);
	}

	public function errorValue(ValueNode $value): ErrorValueNode {
		return new ErrorValueNode($this->getSourceLocation(), $value);
	}

	public function mutableValue(TypeNode $type, ValueNode $value): MutableValueNode {
		return new MutableValueNode($this->getSourceLocation(), $type, $value);
	}

	public function atomValue(TypeNameIdentifier $name): AtomValueNodeInterface {
		return new AtomValueNode($this->getSourceLocation(), $name);
	}

	public function dataValue(TypeNameIdentifier $name, ValueNode $value): DataValueNode {
		return new DataValueNode($this->getSourceLocation(), $name, $value);
	}

	public function enumerationValue(TypeNameIdentifier $name, EnumValueIdentifier $enumValue): EnumerationValueNode {
		return new EnumerationValueNode($this->getSourceLocation(), $name, $enumValue);
	}

	/** @param array<string, ValueNode> $values */
	public function recordValue(array $values): RecordValueNode {
		return new RecordValueNode($this->getSourceLocation(), $values);
	}

	/** @param list<ValueNode> $values */
	public function tupleValue(array $values): TupleValueNode {
		return new TupleValueNode($this->getSourceLocation(), $values);
	}

	/** @param list<ValueNode> $values */
	public function setValue(array $values): SetValueNode {
		return new SetValueNode($this->getSourceLocation(), $values);
	}

	public function functionValue(
		NameAndTypeNodeInterface $parameter,
		NameAndTypeNodeInterface $dependency,
		TypeNode $returnType,
		FunctionBodyNodeInterface $functionBody
	): FunctionValueNode {
		return new FunctionValueNode(
			$this->getSourceLocation(),
			$parameter,
			$dependency,
			$returnType,
			$functionBody
		);
	}
}