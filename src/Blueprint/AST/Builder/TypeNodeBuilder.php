<?php

namespace Walnut\Lang\Blueprint\AST\Builder;

use BcMath\Number;
use Walnut\Lang\Blueprint\AST\Node\NameAndTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\AnyTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\ArrayTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\BooleanTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\BytesTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\EnumerationSubsetTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\FalseTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\FunctionTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\ImpureTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\IntegerFullTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\IntegerSubsetTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\IntegerTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\IntersectionTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\MapTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\MetaTypeTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\MutableTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\NamedTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\NothingTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\NullTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\NumberIntervalNode;
use Walnut\Lang\Blueprint\AST\Node\Type\OptionalKeyTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\ProxyTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\RealFullTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\RealSubsetTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\RealTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\RecordTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\ResultTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\SetTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\ShapeTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\StringSubsetTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\StringTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\TrueTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\TupleTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\UnionTypeNode;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;

interface TypeNodeBuilder {

	/** @param array<string, TypeNode> $types */
	public function recordType(array $types, TypeNode|null $restType = null): RecordTypeNode;
	/** @param list<TypeNode> $types */
	public function tupleType(array $types, TypeNode|null $restType = null): TupleTypeNode;

	/** @param list<EnumValueIdentifier> $values */
	public function enumerationSubsetType(TypeNameIdentifier $name, array $values): EnumerationSubsetTypeNode;
	public function namedType(TypeNameIdentifier $name): NamedTypeNode;
	public function resultType(TypeNode $returnType, TypeNode $errorType): ResultTypeNode;
	public function impureType(TypeNode $valueType): ImpureTypeNode;
	public function mutableType(TypeNode $valueType): MutableTypeNode;
	public function metaTypeType(MetaTypeValue $value): MetaTypeTypeNode;
	public function intersectionType(TypeNode $left, TypeNode $right): IntersectionTypeNode;
	public function unionType(TypeNode $left, TypeNode $right): UnionTypeNode;
	public function shapeType(TypeNode $refType): ShapeTypeNode;
	public function typeType(TypeNode $refType): TypeTypeNode;
	public function proxyType(TypeNameIdentifier $typeName): ProxyTypeNode;
	public function optionalKeyType(TypeNode $valueType): OptionalKeyTypeNode;
	public function functionType(TypeNode $parameterType, TypeNode $returnType): FunctionTypeNode;

	public function numberInterval(
		MinusInfinity|NumberIntervalEndpoint $start,
		PlusInfinity|NumberIntervalEndpoint $end
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
	/** @param list<string> $values */
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

	public function nameAndType(TypeNode $type, VariableNameIdentifier|null $name): NameAndTypeNode;
}