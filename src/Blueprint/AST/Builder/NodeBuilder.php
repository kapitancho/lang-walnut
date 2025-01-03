<?php

namespace Walnut\Lang\Blueprint\AST\Builder;

use BcMath\Number;
use Walnut\Lang\Blueprint\AST\Node\Expression\ConstantExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\ConstructorCallExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\FunctionCallExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchExpressionDefaultNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchExpressionPairNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchIfExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchTrueExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchTypeExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchValueExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MethodCallExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MutableExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\NoErrorExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\NoExternalErrorExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\PropertyAccessExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\RecordExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\ReturnExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\SequenceExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\TupleExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\VariableAssignmentExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\VariableNameExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\FunctionBodyNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddAliasTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddAtomTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddConstructorMethodNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddEnumerationTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddMethodNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddSealedTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddSubtypeTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Module\AddVariableNode;
use Walnut\Lang\Blueprint\AST\Node\Type\AnyTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\ArrayTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\BooleanTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\EnumerationSubsetTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\FalseTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\FunctionTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\ImpureTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\IntegerSubsetTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\IntegerTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\IntersectionTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\MapTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\MetaTypeTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\MutableTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\NamedTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\NothingTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\NullTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\OptionalKeyTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\ProxyTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\RealSubsetTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\RealTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\RecordTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\ResultTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\StringSubsetTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\StringTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\TrueTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\TupleTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\UnionTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Value\AtomValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\EnumerationValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\FalseValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\FunctionValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\IntegerValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\NullValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\RealValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\RecordValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\StringValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\TrueValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\TupleValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\TypeValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\ValueNode;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;

interface NodeBuilder {

	public function constant(ValueNode $value): ConstantExpressionNode;

	public function constructorCall(
		TypeNameIdentifier $typeName,
		ExpressionNode $parameter
	): ConstructorCallExpressionNode;

	public function functionCall(ExpressionNode $target, ExpressionNode $parameter): FunctionCallExpressionNode;

	/** @param list<MatchExpressionPairNode|MatchExpressionDefaultNode> $pairs */
	public function matchTrue(array $pairs): MatchTrueExpressionNode;
	/** @param list<MatchExpressionPairNode|MatchExpressionDefaultNode> $pairs */
	public function matchType(ExpressionNode $target, array $pairs): MatchTypeExpressionNode;
	/** @param list<MatchExpressionPairNode|MatchExpressionDefaultNode> $pairs */
	public function matchValue(ExpressionNode $target, array $pairs): MatchValueExpressionNode;
	public function matchIf(ExpressionNode $condition, ExpressionNode $then, ExpressionNode $else): MatchIfExpressionNode;

	public function mutable(TypeNode $type, ExpressionNode $value): MutableExpressionNode;

	public function noError(ExpressionNode $targetExpression): NoErrorExpressionNode;
	public function noExternalError(ExpressionNode $targetExpression): NoExternalErrorExpressionNode;

	public function propertyAccess(ExpressionNode $target, int|string $propertyName): PropertyAccessExpressionNode;

	public function return(ExpressionNode $returnedExpression): ReturnExpressionNode;

	/** @param list<ExpressionNode> $expressions */
	public function sequence(array $expressions): SequenceExpressionNode;

	public function variableAssignment(
		VariableNameIdentifier $variableName,
		ExpressionNode $assignedExpression
	): VariableAssignmentExpressionNode;

	public function variableName(VariableNameIdentifier $variableName): VariableNameExpressionNode;

	/** @param list<ExpressionNode> $values */
	public function tuple(array $values): TupleExpressionNode;
	/** @param array<string, ExpressionNode> $values */
	public function record(array $values): RecordExpressionNode;

	public function matchPair(ExpressionNode $matchExpression, ExpressionNode $valueExpression): MatchExpressionPairNode;

	public function matchDefault(ExpressionNode $valueExpression): MatchExpressionDefaultNode;

	public function methodCall(
		ExpressionNode $target,
		MethodNameIdentifier $methodName,
		ExpressionNode $parameter
	): MethodCallExpressionNode;

	public function functionBody(ExpressionNode $expression): FunctionBodyNode;

	public function addMethod(
		TypeNode $targetType,
		MethodNameIdentifier $methodName,
		TypeNode $parameterType,
		TypeNode $dependencyType,
		TypeNode $returnType,
		FunctionBodyNode $functionBody,
	): AddMethodNode;

	public function addConstructorMethod(
		TypeNameIdentifier $typeName,
		TypeNode $parameterType,
		TypeNode $dependencyType,
		TypeNode|null $errorType,
		FunctionBodyNode $functionBody,
	): AddConstructorMethodNode;

	public function addVariable(VariableNameIdentifier $name, ValueNode $value): AddVariableNode;

	public function addAtom(TypeNameIdentifier $name): AddAtomTypeNode;

	/** @param list<EnumValueIdentifier> $values */
	public function addEnumeration(TypeNameIdentifier $name, array $values): AddEnumerationTypeNode;

	public function addAlias(TypeNameIdentifier $name, TypeNode $aliasedType): AddAliasTypeNode;

	public function addSealed(
		TypeNameIdentifier $name,
		RecordTypeNode $valueType,
		ExpressionNode $constructorBody,
		TypeNode|null $errorType
	): AddSealedTypeNode;

	public function addSubtype(
		TypeNameIdentifier $name,
		TypeNode $baseType,
		ExpressionNode $constructorBody,
		TypeNode|null $errorType
	): AddSubtypeTypeNode;

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
	public function typeType(TypeNode $refType): TypeTypeNode;
	public function proxyType(TypeNameIdentifier $typeName): ProxyTypeNode;
	public function optionalKeyType(TypeNode $valueType): OptionalKeyTypeNode;
	public function functionType(TypeNode $parameterType, TypeNode $returnType): FunctionTypeNode;

	public function integerType(
		Number|MinusInfinity $minValue = MinusInfinity::value,
		Number|PlusInfinity $maxValue = PlusInfinity::value
	): IntegerTypeNode;
	/** @param list<IntegerValueNode> $values */
	public function integerSubsetType(array $values): IntegerSubsetTypeNode;

	public function realType(
		Number|MinusInfinity $minValue = MinusInfinity::value,
		Number|PlusInfinity $maxValue = PlusInfinity::value
	): RealTypeNode;
	/** @param list<RealValueNode> $values */
	public function realSubsetType(array $values): RealSubsetTypeNode;

	public function stringType(
		Number $minLength = new Number(0),
		Number|PlusInfinity $maxLength = PlusInfinity::value
	): StringTypeNode;
	/** @param list<StringValueNode> $values */
	public function stringSubsetType(array $values): StringSubsetTypeNode;

	public function arrayType(
		TypeNode|null $itemType = null,
		Number $minLength = new Number(0),
		Number|PlusInfinity $maxLength = PlusInfinity::value
	): ArrayTypeNode;
	public function mapType(
		TypeNode|null $itemType = null,
		Number $minLength = new Number(0),
		Number|PlusInfinity $maxLength = PlusInfinity::value
	): MapTypeNode;

	public AnyTypeNode $anyType { get; }
	public NothingTypeNode $nothingType { get; }
	public NullTypeNode $nullType { get; }
	public TrueTypeNode $trueType { get; }
	public FalseTypeNode $falseType { get; }
	public BooleanTypeNode $booleanType { get; }


	public NullValueNode $nullValue { get; }
	public TrueValueNode $trueValue { get; }
	public FalseValueNode $falseValue { get; }
	public function integerValue(Number $value): IntegerValueNode;
	public function realValue(Number $value): RealValueNode;
	public function stringValue(string $value): StringValueNode;
	public function typeValue(TypeNode $type): TypeValueNode;
	public function atomValue(TypeNameIdentifier $name): AtomValueNode;
	public function enumerationValue(TypeNameIdentifier $name, EnumValueIdentifier $enumValue): EnumerationValueNode;
	/** @param array<string, ValueNode> $values */
	public function recordValue(array $values): RecordValueNode;
	/** @param list<ValueNode> $values */
	public function tupleValue(array $values): TupleValueNode;
	public function functionValue(
		TypeNode $parameterType,
		TypeNode $dependencyType,
		TypeNode $returnType,
		FunctionBodyNode $functionBody
	): FunctionValueNode;
}