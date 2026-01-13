<?php

namespace Walnut\Lang\Implementation\AST\Builder;

use BcMath\Number;
use Walnut\Lang\Blueprint\AST\Builder\TypeNodeBuilder as TypeNodeBuilderInterface;
use Walnut\Lang\Blueprint\AST\Builder\SourceLocator;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation as SourceLocationInterface;
use Walnut\Lang\Blueprint\AST\Node\Type\NumberIntervalNode as NumberIntervalNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;
use Walnut\Lang\Implementation\AST\Node\NameAndTypeNode;
use Walnut\Lang\Implementation\AST\Node\SourceLocation;
use Walnut\Lang\Implementation\AST\Node\Type\AnyTypeNode;
use Walnut\Lang\Implementation\AST\Node\Type\ArrayTypeNode;
use Walnut\Lang\Implementation\AST\Node\Type\BooleanTypeNode;
use Walnut\Lang\Implementation\AST\Node\Type\BytesTypeNode;
use Walnut\Lang\Implementation\AST\Node\Type\EnumerationSubsetTypeNode;
use Walnut\Lang\Implementation\AST\Node\Type\FalseTypeNode;
use Walnut\Lang\Implementation\AST\Node\Type\FunctionTypeNode;
use Walnut\Lang\Implementation\AST\Node\Type\ImpureTypeNode;
use Walnut\Lang\Implementation\AST\Node\Type\IntegerFullTypeNode;
use Walnut\Lang\Implementation\AST\Node\Type\IntegerSubsetTypeNode;
use Walnut\Lang\Implementation\AST\Node\Type\IntegerTypeNode;
use Walnut\Lang\Implementation\AST\Node\Type\IntersectionTypeNode;
use Walnut\Lang\Implementation\AST\Node\Type\MapTypeNode;
use Walnut\Lang\Implementation\AST\Node\Type\MetaTypeTypeNode;
use Walnut\Lang\Implementation\AST\Node\Type\MutableTypeNode;
use Walnut\Lang\Implementation\AST\Node\Type\NamedTypeNode;
use Walnut\Lang\Implementation\AST\Node\Type\NothingTypeNode;
use Walnut\Lang\Implementation\AST\Node\Type\NullTypeNode;
use Walnut\Lang\Implementation\AST\Node\Type\NumberIntervalNode;
use Walnut\Lang\Implementation\AST\Node\Type\OptionalKeyTypeNode;
use Walnut\Lang\Implementation\AST\Node\Type\ProxyTypeNode;
use Walnut\Lang\Implementation\AST\Node\Type\RealFullTypeNode;
use Walnut\Lang\Implementation\AST\Node\Type\RealSubsetTypeNode;
use Walnut\Lang\Implementation\AST\Node\Type\RealTypeNode;
use Walnut\Lang\Implementation\AST\Node\Type\RecordTypeNode;
use Walnut\Lang\Implementation\AST\Node\Type\ResultTypeNode;
use Walnut\Lang\Implementation\AST\Node\Type\SetTypeNode;
use Walnut\Lang\Implementation\AST\Node\Type\ShapeTypeNode;
use Walnut\Lang\Implementation\AST\Node\Type\StringSubsetTypeNode;
use Walnut\Lang\Implementation\AST\Node\Type\StringTypeNode;
use Walnut\Lang\Implementation\AST\Node\Type\TrueTypeNode;
use Walnut\Lang\Implementation\AST\Node\Type\TupleTypeNode;
use Walnut\Lang\Implementation\AST\Node\Type\TypeTypeNode;
use Walnut\Lang\Implementation\AST\Node\Type\UnionTypeNode;

final class TypeNodeBuilder implements TypeNodeBuilderInterface {

	public function __construct(private readonly SourceLocator $sourceLocator) {}

	private function getSourceLocation(): SourceLocationInterface {
		return $this->sourceLocator->getSourceLocation();
	}

	public AnyTypeNode $anyType {
		get {
			return new AnyTypeNode($this->getSourceLocation());
		}
	}
	public NothingTypeNode $nothingType {
		get {
			return new NothingTypeNode($this->getSourceLocation());
		}
	}
	public NullTypeNode $nullType {
		get {
			return new NullTypeNode($this->getSourceLocation());
		}
	}
	public TrueTypeNode $trueType {
		get {
			return new TrueTypeNode($this->getSourceLocation());
		}
	}
	public FalseTypeNode $falseType {
		get {
			return new FalseTypeNode($this->getSourceLocation());
		}
	}
	public BooleanTypeNode $booleanType {
		get {
			return new BooleanTypeNode($this->getSourceLocation());
		}
	}

	/** @param array<string, TypeNode> $types */
	public function recordType(array $types, TypeNode|null $restType = null): RecordTypeNode {
		return new RecordTypeNode(
			$this->getSourceLocation(),
			$types,
			$restType ?? $this->nothingType
		);
	}

	/** @param list<TypeNode> $types */
	public function tupleType(array $types, TypeNode|null $restType = null): TupleTypeNode {
		return new TupleTypeNode(
			$this->getSourceLocation(),
			$types,
			$restType ?? $this->nothingType
		);
	}

	/** @param list<EnumValueIdentifier> $values */
	public function enumerationSubsetType(TypeNameIdentifier $name, array $values): EnumerationSubsetTypeNode {
		return new EnumerationSubsetTypeNode(
			$this->getSourceLocation(),
			$name,
			$values
		);
	}

	public function namedType(TypeNameIdentifier $name): NamedTypeNode {
		return new NamedTypeNode(
			$this->getSourceLocation(),
			$name
		);
	}

	public function resultType(TypeNode $returnType, TypeNode $errorType): ResultTypeNode {
		return new ResultTypeNode(
			$this->getSourceLocation(),
			$returnType,
			$errorType
		);
	}

	public function impureType(TypeNode $valueType): ImpureTypeNode {
		return new ImpureTypeNode(
			$this->getSourceLocation(),
			$valueType
		);
	}

	public function mutableType(TypeNode $valueType): MutableTypeNode {
		return new MutableTypeNode(
			$this->getSourceLocation(),
			$valueType
		);
	}

	public function metaTypeType(MetaTypeValue $value): MetaTypeTypeNode {
		return new MetaTypeTypeNode(
			$this->getSourceLocation(),
			$value
		);
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
		return new ShapeTypeNode(
			$this->getSourceLocation(),
			$refType
		);
	}

	public function typeType(TypeNode $refType): TypeTypeNode {
		return new TypeTypeNode(
			$this->getSourceLocation(),
			$refType
		);
	}

	public function proxyType(TypeNameIdentifier $typeName): ProxyTypeNode {
		return new ProxyTypeNode(
			$this->getSourceLocation(),
			$typeName
		);
	}

	public function optionalKeyType(TypeNode $valueType): OptionalKeyTypeNode {
		return new OptionalKeyTypeNode(
			$this->getSourceLocation(),
			$valueType
		);
	}

	public function functionType(TypeNode $parameterType, TypeNode $returnType): FunctionTypeNode {
		return new FunctionTypeNode(
			$this->getSourceLocation(),
			$parameterType,
			$returnType
		);
	}

	public function numberInterval(
		MinusInfinity|NumberIntervalEndpoint $start,
		PlusInfinity|NumberIntervalEndpoint $end
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
		return new ArrayTypeNode(
			$this->getSourceLocation(),
			$itemType ?? $this->anyType,
			$minLength,
			$maxLength
		);
	}

	public function mapType(
		TypeNode|null $keyType = null,
		TypeNode|null $itemType = null,
		Number $minLength = new Number(0),
		PlusInfinity|Number $maxLength = PlusInfinity::value
	): MapTypeNode {
		return new MapTypeNode(
			$this->getSourceLocation(),
			$keyType ?? $this->stringType(),
			$itemType ?? $this->anyType,
			$minLength,
			$maxLength
		);
	}

	public function setType(
		TypeNode|null $itemType = null,
		Number $minLength = new Number(0),
		Number|PlusInfinity $maxLength = PlusInfinity::value
	): SetTypeNode {
		return new SetTypeNode(
			$this->getSourceLocation(),
			$itemType ?? $this->anyType,
			$minLength,
			$maxLength
		);
	}

	public function nameAndType(TypeNode $type, VariableNameIdentifier|null $name): NameAndTypeNode {
		return new NameAndTypeNode($this->getSourceLocation(), $type, $name);
	}

}