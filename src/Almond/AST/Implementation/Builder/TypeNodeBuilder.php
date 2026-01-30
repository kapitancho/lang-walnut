<?php

namespace Walnut\Lang\Almond\AST\Implementation\Builder;

use BcMath\Number;
use Walnut\Lang\Almond\AST\Blueprint\Builder\SourceLocator;
use Walnut\Lang\Almond\AST\Blueprint\Builder\TypeNodeBuilder as TypeNodeBuilderInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\EnumerationValueNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\TypeNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\VariableNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation as SourceLocationInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\NumberIntervalEndpointNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\NumberIntervalNode as NumberIntervalNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\StringValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Number\MinusInfinity;
use Walnut\Lang\Almond\AST\Blueprint\Number\PlusInfinity;
use Walnut\Lang\Almond\AST\Implementation\Node\NameAndTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\AnyTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\ArrayTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\BooleanTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\BytesTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\EnumerationSubsetTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\FalseTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\FunctionTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\ImpureTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\IntegerFullTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\IntegerSubsetTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\IntegerTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\IntersectionTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\MapTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\MetaTypeTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\MutableTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\NamedTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\NothingTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\NullTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\NumberIntervalNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\OptionalKeyTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\ProxyTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\RealFullTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\RealSubsetTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\RealTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\RecordTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\ResultTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\SetTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\ShapeTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\StringSubsetTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\StringTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\TrueTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\TupleTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\TypeTypeNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Type\UnionTypeNode;

final class TypeNodeBuilder implements TypeNodeBuilderInterface {

	public function __construct(private readonly SourceLocator $sourceLocator) {}

	private function getSourceLocation(): SourceLocationInterface {
		return $this->sourceLocator->getSourceLocation();
	}

	public AnyTypeNode $anyType {
		get { return new AnyTypeNode($this->getSourceLocation()); }
	}
	public NothingTypeNode $nothingType {
		get { return new NothingTypeNode($this->getSourceLocation()); }
	}
	public NullTypeNode $nullType {
		get { return new NullTypeNode($this->getSourceLocation()); }
	}
	public TrueTypeNode $trueType {
		get { return new TrueTypeNode($this->getSourceLocation()); }
	}
	public FalseTypeNode $falseType {
		get { return new FalseTypeNode($this->getSourceLocation()); }
	}
	public BooleanTypeNode $booleanType {
		get { return new BooleanTypeNode($this->getSourceLocation()); }
	}

	/** @param array<string, TypeNode> $types */
	public function recordType(array $types, TypeNode|null $restType = null): RecordTypeNode {
		return new RecordTypeNode($this->getSourceLocation(),
			$types, $restType ?? $this->nothingType
		);
	}

	/** @param list<TypeNode> $types */
	public function tupleType(array $types, TypeNode|null $restType = null): TupleTypeNode {
		return new TupleTypeNode($this->getSourceLocation(),
			$types, $restType ?? $this->nothingType
		);
	}

	/** @param list<EnumerationValueNameNode> $values */
	public function enumerationSubsetType(TypeNameNode $name, array $values): EnumerationSubsetTypeNode {
		return new EnumerationSubsetTypeNode($this->getSourceLocation(), $name, $values);
	}

	public function namedType(TypeNameNode $name): NamedTypeNode {
		return new NamedTypeNode($this->getSourceLocation(), $name);
	}

	public function resultType(TypeNode $returnType, TypeNode $errorType): ResultTypeNode {
		return new ResultTypeNode($this->getSourceLocation(), $returnType, $errorType);
	}

	public function impureType(TypeNode $valueType): ImpureTypeNode {
		return new ImpureTypeNode($this->getSourceLocation(), $valueType);
	}

	public function mutableType(TypeNode $valueType): MutableTypeNode {
		return new MutableTypeNode($this->getSourceLocation(), $valueType);
	}

	public function metaTypeType(string $value): MetaTypeTypeNode {
		return new MetaTypeTypeNode($this->getSourceLocation(), $value);
	}

	public function intersectionType(TypeNode $left, TypeNode $right): IntersectionTypeNode {
		return new IntersectionTypeNode(
			new SourceLocation(
				$left->sourceLocation->moduleName,
				$left->sourceLocation->startPosition,
				$right->sourceLocation->endPosition
			),
			$left,
			$right
		);
	}

	public function unionType(TypeNode $left, TypeNode $right): UnionTypeNode {
		return new UnionTypeNode(
			new SourceLocation(
				$left->sourceLocation->moduleName,
				$left->sourceLocation->startPosition,
				$right->sourceLocation->endPosition
			),
			$left,
			$right
		);
	}

	public function shapeType(TypeNode $refType): ShapeTypeNode {
		return new ShapeTypeNode($this->getSourceLocation(),
			$refType
		);
	}

	public function typeType(TypeNode $refType): TypeTypeNode {
		return new TypeTypeNode($this->getSourceLocation(),
			$refType
		);
	}

	public function proxyType(TypeNameNode $typeName): ProxyTypeNode {
		return new ProxyTypeNode($this->getSourceLocation(),
			$typeName
		);
	}

	public function optionalKeyType(TypeNode $valueType): OptionalKeyTypeNode {
		return new OptionalKeyTypeNode($this->getSourceLocation(),
			$valueType
		);
	}

	public function functionType(TypeNode $parameterType, TypeNode $returnType): FunctionTypeNode {
		return new FunctionTypeNode($this->getSourceLocation(),
			$parameterType, $returnType
		);
	}

	public function numberInterval(
		MinusInfinity|NumberIntervalEndpointNode $start,
		PlusInfinity|NumberIntervalEndpointNode  $end
	): NumberIntervalNodeInterface {
		return new NumberIntervalNode($this->getSourceLocation(), $start, $end);
	}

	/** @param NumberIntervalNodeInterface[] $intervals */
	public function integerFullType(array $intervals): IntegerFullTypeNode {
		return new IntegerFullTypeNode($this->getSourceLocation(), $intervals);
	}

	public function integerType(Number|MinusInfinity $minValue = MinusInfinity::value, PlusInfinity|Number $maxValue = PlusInfinity::value): IntegerTypeNode {
		return new IntegerTypeNode($this->getSourceLocation(), $minValue, $maxValue);
	}

	public function integerSubsetType(array $values): IntegerSubsetTypeNode {
		return new IntegerSubsetTypeNode($this->getSourceLocation(), $values);
	}

	/** @param NumberIntervalNodeInterface[] $intervals */
	public function realFullType(array $intervals): RealFullTypeNode {
		return new RealFullTypeNode($this->getSourceLocation(), $intervals);
	}

	public function realType(
		Number|MinusInfinity $minValue = MinusInfinity::value,
		PlusInfinity|Number $maxValue = PlusInfinity::value
	): RealTypeNode {
		return new RealTypeNode($this->getSourceLocation(), $minValue, $maxValue);
	}

	public function realSubsetType(array $values): RealSubsetTypeNode {
		return new RealSubsetTypeNode($this->getSourceLocation(), $values);
	}

	public function stringType(
		Number $minLength = new Number(0),
		PlusInfinity|Number $maxLength = PlusInfinity::value
	): StringTypeNode {
		return new StringTypeNode($this->getSourceLocation(), $minLength, $maxLength);
	}

	/** @param list<StringValueNode> $values */
	public function stringSubsetType(array $values): StringSubsetTypeNode {
		return new StringSubsetTypeNode($this->getSourceLocation(), $values);
	}

	public function bytesType(
		Number $minLength = new Number(0),
		PlusInfinity|Number $maxLength = PlusInfinity::value
	): BytesTypeNode {
		return new BytesTypeNode($this->getSourceLocation(), $minLength, $maxLength);
	}

	public function arrayType(
		TypeNode|null $itemType = null,
		Number $minLength = new Number(0),
		PlusInfinity|Number $maxLength = PlusInfinity::value
	): ArrayTypeNode {
		return new ArrayTypeNode($this->getSourceLocation(),
			$itemType ?? $this->anyType, $minLength, $maxLength
		);
	}

	public function mapType(
		TypeNode|null $keyType = null,
		TypeNode|null $itemType = null,
		Number $minLength = new Number(0),
		PlusInfinity|Number $maxLength = PlusInfinity::value
	): MapTypeNode {
		return new MapTypeNode($this->getSourceLocation(),
			$keyType ?? $this->stringType(), $itemType ?? $this->anyType,
			$minLength, $maxLength
		);
	}

	public function setType(
		TypeNode|null $itemType = null,
		Number $minLength = new Number(0),
		Number|PlusInfinity $maxLength = PlusInfinity::value
	): SetTypeNode {
		return new SetTypeNode($this->getSourceLocation(),
			$itemType ?? $this->anyType, $minLength, $maxLength
		);
	}

	public function nameAndType(TypeNode $type, VariableNameNode|null $name): NameAndTypeNode {
		return new NameAndTypeNode($this->getSourceLocation(), $type, $name);
	}
}