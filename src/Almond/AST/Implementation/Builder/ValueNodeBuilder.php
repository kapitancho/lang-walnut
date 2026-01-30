<?php

namespace Walnut\Lang\Almond\AST\Implementation\Builder;

use BcMath\Number;
use Walnut\Lang\Almond\AST\Blueprint\Builder\SourceLocator;
use Walnut\Lang\Almond\AST\Blueprint\Builder\ValueNodeBuilder as ValueNodeBuilderInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\FunctionBodyNode as FunctionBodyNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\EnumerationValueNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\TypeNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\NameAndTypeNode as NameAndTypeNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation as SourceLocationInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\AtomValueNode as AtomValueNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\ValueNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Value\AtomValueNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Value\BytesValueNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Value\DataValueNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Value\EnumerationValueNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Value\ErrorValueNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Value\FalseValueNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Value\FunctionValueNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Value\IntegerValueNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Value\MutableValueNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Value\NullValueNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Value\RealValueNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Value\RecordValueNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Value\SetValueNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Value\StringValueNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Value\TrueValueNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Value\TupleValueNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Value\TypeValueNode;

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

	public function atomValue(TypeNameNode $name): AtomValueNodeInterface {
		return new AtomValueNode($this->getSourceLocation(), $name);
	}

	public function dataValue(TypeNameNode $name, ValueNode $value): DataValueNode {
		return new DataValueNode($this->getSourceLocation(), $name, $value);
	}

	public function enumerationValue(TypeNameNode $name, EnumerationValueNameNode $enumValue): EnumerationValueNode {
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