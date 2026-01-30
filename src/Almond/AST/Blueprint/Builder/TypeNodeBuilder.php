<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Builder;

use BcMath\Number;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\EnumerationValueNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\TypeNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\VariableNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\NameAndTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\AnyTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\ArrayTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\BooleanTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\BytesTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\EnumerationSubsetTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\FalseTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\FunctionTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\ImpureTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\IntegerFullTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\IntegerSubsetTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\IntegerTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\IntersectionTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\MapTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\MetaTypeTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\MutableTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\NamedTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\NothingTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\NullTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\NumberIntervalNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\OptionalKeyTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\ProxyTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\RealFullTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\RealSubsetTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\RealTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\RecordTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\ResultTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\SetTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\ShapeTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\StringSubsetTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\StringTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TrueTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TupleTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\UnionTypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\StringValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Number\MinusInfinity;
use Walnut\Lang\Almond\AST\Blueprint\Node\Type\NumberIntervalEndpointNode;
use Walnut\Lang\Almond\AST\Blueprint\Number\PlusInfinity;

interface TypeNodeBuilder {

	/** @param array<string, TypeNode> $types */
	public function recordType(array $types, TypeNode|null $restType = null): RecordTypeNode;
	/** @param list<TypeNode> $types */
	public function tupleType(array $types, TypeNode|null $restType = null): TupleTypeNode;

	/** @param list<EnumerationValueNameNode> $values */
	public function enumerationSubsetType(TypeNameNode $name, array $values): EnumerationSubsetTypeNode;
	public function namedType(TypeNameNode $name): NamedTypeNode;
	public function resultType(TypeNode $returnType, TypeNode $errorType): ResultTypeNode;
	public function impureType(TypeNode $valueType): ImpureTypeNode;
	public function mutableType(TypeNode $valueType): MutableTypeNode;
	public function metaTypeType(string $value): MetaTypeTypeNode;
	public function intersectionType(TypeNode $left, TypeNode $right): IntersectionTypeNode;
	public function unionType(TypeNode $left, TypeNode $right): UnionTypeNode;
	public function shapeType(TypeNode $refType): ShapeTypeNode;
	public function typeType(TypeNode $refType): TypeTypeNode;
	public function proxyType(TypeNameNode $typeName): ProxyTypeNode;
	public function optionalKeyType(TypeNode $valueType): OptionalKeyTypeNode;
	public function functionType(TypeNode $parameterType, TypeNode $returnType): FunctionTypeNode;

	public function numberInterval(
		MinusInfinity|NumberIntervalEndpointNode $start,
		PlusInfinity|NumberIntervalEndpointNode  $end
	): NumberIntervalNode;

	/** @param NumberIntervalNode[] $intervals */
	public function integerFullType(array $intervals): IntegerFullTypeNode;
	public function integerType(
		Number|MinusInfinity $minValue = MinusInfinity::value,
		Number|PlusInfinity $maxValue = PlusInfinity::value
	): IntegerTypeNode;
	/** @param list<Number> $values */
	public function integerSubsetType(array $values): IntegerSubsetTypeNode;

	/** @param NumberIntervalNode[] $intervals */
	public function realFullType(array $intervals): RealFullTypeNode;
	public function realType(
		Number|MinusInfinity $minValue = MinusInfinity::value,
		Number|PlusInfinity $maxValue = PlusInfinity::value
	): RealTypeNode;
	/** @param list<Number> $values */
	public function realSubsetType(array $values): RealSubsetTypeNode;

	public function stringType(
		Number $minLength = new Number(0),
		Number|PlusInfinity $maxLength = PlusInfinity::value
	): StringTypeNode;

	/** @param list<StringValueNode> $values */
	public function stringSubsetType(array $values): StringSubsetTypeNode;

	public function bytesType(
		Number $minLength = new Number(0),
		PlusInfinity|Number $maxLength = PlusInfinity::value
	): BytesTypeNode;

	public function arrayType(
		TypeNode|null $itemType = null,
		Number $minLength = new Number(0),
		Number|PlusInfinity $maxLength = PlusInfinity::value
	): ArrayTypeNode;
	public function mapType(
		TypeNode|null $keyType = null,
		TypeNode|null $itemType = null,
		Number $minLength = new Number(0),
		Number|PlusInfinity $maxLength = PlusInfinity::value
	): MapTypeNode;
	public function setType(
		TypeNode|null $itemType = null,
		Number $minLength = new Number(0),
		Number|PlusInfinity $maxLength = PlusInfinity::value
	): SetTypeNode;

	public AnyTypeNode $anyType { get; }
	public NothingTypeNode $nothingType { get; }
	public NullTypeNode $nullType { get; }
	public TrueTypeNode $trueType { get; }
	public FalseTypeNode $falseType { get; }
	public BooleanTypeNode $booleanType { get; }

	public function nameAndType(TypeNode $type, VariableNameNode|null $name): NameAndTypeNode;
}