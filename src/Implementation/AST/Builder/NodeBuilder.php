<?php

namespace Walnut\Lang\Implementation\AST\Builder;

use BcMath\Number;
use Walnut\Lang\Blueprint\AST\Builder\NodeBuilder as NodeBuilderInterface;
use Walnut\Lang\Blueprint\AST\Builder\SourceLocator;
use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchExpressionDefaultNode as MatchExpressionDefaultNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Expression\MatchExpressionPairNode as MatchExpressionPairNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Expression\MethodCallExpressionNode as MethodCallExpressionNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Expression\MultiVariableAssignmentExpressionNode as MultiVariableAssignmentExpressionNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\FunctionBodyNode as FunctionBodyNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Module\ModuleDefinitionNode;
use Walnut\Lang\Blueprint\AST\Node\NameAndTypeNode as NameAndTypeNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\SourceLocation as SourceLocationInterface;
use Walnut\Lang\Blueprint\AST\Node\Type\NumberIntervalNode as NumberIntervalNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\AST\Node\Value\AtomValueNode as AtomValueNodeInterface;
use Walnut\Lang\Blueprint\AST\Node\Value\ValueNode;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;
use Walnut\Lang\Implementation\AST\Node\Expression\ConstantExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\ConstructorCallExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\DataExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\ScopedExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\FunctionCallExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\MatchErrorExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\MatchExpressionDefaultNode;
use Walnut\Lang\Implementation\AST\Node\Expression\MatchExpressionPairNode;
use Walnut\Lang\Implementation\AST\Node\Expression\MatchIfExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\MatchTrueExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\MatchTypeExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\MatchValueExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\MethodCallExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\MultiVariableAssignmentExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\MutableExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\NoErrorExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\NoExternalErrorExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\PropertyAccessExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\RecordExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\ReturnExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\SequenceExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\SetExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\TupleExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\VariableAssignmentExpressionNode;
use Walnut\Lang\Implementation\AST\Node\Expression\VariableNameExpressionNode;
use Walnut\Lang\Implementation\AST\Node\FunctionBodyNode;
use Walnut\Lang\Implementation\AST\Node\Module\AddAliasTypeNode;
use Walnut\Lang\Implementation\AST\Node\Module\AddAtomTypeNode;
use Walnut\Lang\Implementation\AST\Node\Module\AddConstructorMethodNode;
use Walnut\Lang\Implementation\AST\Node\Module\AddDataTypeNode;
use Walnut\Lang\Implementation\AST\Node\Module\AddEnumerationTypeNode;
use Walnut\Lang\Implementation\AST\Node\Module\AddMethodNode;
use Walnut\Lang\Implementation\AST\Node\Module\AddOpenTypeNode;
use Walnut\Lang\Implementation\AST\Node\Module\AddSealedTypeNode;
use Walnut\Lang\Implementation\AST\Node\Module\ModuleNode;
use Walnut\Lang\Implementation\AST\Node\NameAndTypeNode;
use Walnut\Lang\Implementation\AST\Node\SourceLocation;
use Walnut\Lang\Implementation\AST\Node\Type\AnyTypeNode;
use Walnut\Lang\Implementation\AST\Node\Type\ArrayTypeNode;
use Walnut\Lang\Implementation\AST\Node\Type\BooleanTypeNode;
use Walnut\Lang\Implementation\AST\Node\Type\ByteArrayTypeNode;
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
use Walnut\Lang\Implementation\AST\Node\Value\AtomValueNode;
use Walnut\Lang\Implementation\AST\Node\Value\ByteArrayValueNode;
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

final class NodeBuilder implements NodeBuilderInterface {
	private string $moduleName;
	/** @param list<string> $moduleDependencies */
	private array $moduleDependencies = [];
	/** @param list<ModuleDefinitionNode> $definitions */
	private array $definitions = [];

	public function __construct(
		private readonly SourceLocator $sourceLocator
	) {
		$this->moduleName = $this->sourceLocator->getSourceLocation()->moduleName;
	}

	public function moduleName(string $moduleName): self {
		$this->moduleName = $moduleName;
		return $this;
	}

	public function moduleDependencies(array $dependencies): self {
		$this->moduleDependencies = $dependencies;
		return $this;
	}

	public function definition(ModuleDefinitionNode $definition): self {
		$this->definitions[] = $definition;
		return $this;
	}

	public function build(): ModuleNode {
		return new ModuleNode(
			$this->moduleName,
			$this->moduleDependencies,
			$this->definitions
		);
	}

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

	public function constant(ValueNode $value): ConstantExpressionNode {
		return new ConstantExpressionNode($this->getSourceLocation(), $value);
	}

	public function data(TypeNameIdentifier $typeName, ExpressionNode $value): DataExpressionNode {
		return new DataExpressionNode($this->getSourceLocation(), $typeName, $value);
	}

	public function constructorCall(
		TypeNameIdentifier $typeName,
		ExpressionNode $parameter
	): ConstructorCallExpressionNode {
		return new ConstructorCallExpressionNode(
			$this->getSourceLocation(),
			$typeName,
			$parameter
		);
	}

	public function functionCall(ExpressionNode $target, ExpressionNode $parameter): FunctionCallExpressionNode {
		return new FunctionCallExpressionNode($this->getSourceLocation(), $target, $parameter);
	}

	/** @param list<MatchExpressionPairNodeInterface|MatchExpressionDefaultNodeInterface> $pairs */
	public function matchTrue(array $pairs): MatchTrueExpressionNode {
		return new MatchTrueExpressionNode($this->getSourceLocation(), $pairs);
	}

	/** @param list<MatchExpressionPairNodeInterface|MatchExpressionDefaultNodeInterface> $pairs */
	public function matchType(ExpressionNode $target, array $pairs): MatchTypeExpressionNode {
		return new MatchTypeExpressionNode($this->getSourceLocation(), $target, $pairs);
	}

	/** @param list<MatchExpressionPairNodeInterface|MatchExpressionDefaultNodeInterface> $pairs */
	public function matchValue(ExpressionNode $target, array $pairs): MatchValueExpressionNode {
		return new MatchValueExpressionNode($this->getSourceLocation(), $target, $pairs);
	}

	public function matchIf(ExpressionNode $condition, ExpressionNode $then, ExpressionNode $else): MatchIfExpressionNode {
		return new MatchIfExpressionNode($this->getSourceLocation(), $condition, $then, $else);
	}

	public function matchError(ExpressionNode $condition, ExpressionNode $then, ExpressionNode|null $else): MatchErrorExpressionNode {
		return new MatchErrorExpressionNode($this->getSourceLocation(), $condition, $then, $else);
	}

	public function scoped(ExpressionNode $targetExpression): ScopedExpressionNode {
		return new ScopedExpressionNode($this->getSourceLocation(), $targetExpression);
	}

	public function mutable(TypeNode $type, ExpressionNode $value): MutableExpressionNode {
		return new MutableExpressionNode($this->getSourceLocation(), $type, $value);
	}

	public function noError(ExpressionNode $targetExpression): NoErrorExpressionNode {
		return new NoErrorExpressionNode($this->getSourceLocation(), $targetExpression);
	}

	public function noExternalError(ExpressionNode $targetExpression): NoExternalErrorExpressionNode {
		return new NoExternalErrorExpressionNode($this->getSourceLocation(), $targetExpression);
	}

	public function propertyAccess(ExpressionNode $target, int|string $propertyName): PropertyAccessExpressionNode {
		return new PropertyAccessExpressionNode($this->getSourceLocation(), $target, $propertyName);
	}

	public function return(ExpressionNode $returnedExpression): ReturnExpressionNode {
		return new ReturnExpressionNode($this->getSourceLocation(), $returnedExpression);
	}

	/** @param list<ExpressionNode> $expressions */
	public function sequence(array $expressions): SequenceExpressionNode {
		return new SequenceExpressionNode($this->getSourceLocation(), $expressions);
	}

	public function variableAssignment(VariableNameIdentifier $variableName, ExpressionNode $assignedExpression): VariableAssignmentExpressionNode {
		return new VariableAssignmentExpressionNode($this->getSourceLocation(), $variableName, $assignedExpression);
	}

	public function variableName(VariableNameIdentifier $variableName): VariableNameExpressionNode {
		return new VariableNameExpressionNode($this->getSourceLocation(), $variableName);
	}

	/** @param list<ExpressionNode> $values */
	public function tuple(array $values): TupleExpressionNode {
		return new TupleExpressionNode($this->getSourceLocation(), $values);
	}

	/** @param array<string, ExpressionNode> $values */
	public function record(array $values): RecordExpressionNode {
		return new RecordExpressionNode($this->getSourceLocation(), $values);
	}

	/** @param list<ExpressionNode> $values */
	public function set(array $values): SetExpressionNode {
		return new SetExpressionNode($this->getSourceLocation(), $values);
	}

	public function matchPair(ExpressionNode $matchExpression, ExpressionNode $valueExpression): MatchExpressionPairNode {
		return new MatchExpressionPairNode($this->getSourceLocation(), $matchExpression, $valueExpression);
	}

	public function matchDefault(ExpressionNode $valueExpression): MatchExpressionDefaultNode {
		return new MatchExpressionDefaultNode($this->getSourceLocation(), $valueExpression);
	}

	public function methodCall(ExpressionNode $target, MethodNameIdentifier $methodName, ExpressionNode $parameter): MethodCallExpressionNodeInterface {
		return new MethodCallExpressionNode($this->getSourceLocation(), $target, $methodName, $parameter);
	}

	public function functionBody(ExpressionNode $expression): FunctionBodyNode {
		return new FunctionBodyNode($this->getSourceLocation(), $expression);
	}

	public function addMethod(
		TypeNode $targetType,
		MethodNameIdentifier $methodName,
		NameAndTypeNodeInterface $parameter,
		NameAndTypeNodeInterface $dependency,
		TypeNode $returnType,
		FunctionBodyNodeInterface $functionBody
	): AddMethodNode {
		return new AddMethodNode(
			$this->getSourceLocation(),
			$targetType,
			$methodName,
			$parameter,
			$dependency,
			$returnType,
			$functionBody
		);
	}

	public function addConstructorMethod(
		TypeNameIdentifier $typeName,
		NameAndTypeNodeInterface $parameter,
		NameAndTypeNodeInterface $dependency,
		TypeNode|null $errorType,
		FunctionBodyNodeInterface $functionBody
	): AddConstructorMethodNode {
		return new AddConstructorMethodNode(
			$this->getSourceLocation(),
			$typeName,
			$parameter,
			$dependency,
			$errorType ?? $this->nothingType,
			$functionBody
		);
	}

	public function addAtom(TypeNameIdentifier $name): AddAtomTypeNode {
		return new AddAtomTypeNode($this->getSourceLocation(), $name);
	}

	/** @param list<EnumValueIdentifier> $values */
	public function addEnumeration(TypeNameIdentifier $name, array $values): AddEnumerationTypeNode {
		return new AddEnumerationTypeNode($this->getSourceLocation(), $name, $values);
	}

	public function addAlias(TypeNameIdentifier $name, TypeNode $aliasedType): AddAliasTypeNode {
		return new AddAliasTypeNode($this->getSourceLocation(), $name, $aliasedType);
	}

	private function generateConstructorBody(ExpressionNode|null $constructorBody): FunctionBodyNode|null {
		return $constructorBody ? $this->functionBody(
			$this->sequence([
				$constructorBody,
				$this->variableName(new VariableNameIdentifier('#'))
			])
		) : null;
	}

	public function addData(
		TypeNameIdentifier $name,
		TypeNode $valueType,
	): AddDataTypeNode {
		return new AddDataTypeNode(
			$this->getSourceLocation(),
			$name,
			$valueType,
		);
	}

	public function addOpen(
		TypeNameIdentifier $name,
		TypeNode $valueType,
		ExpressionNode|null $constructorBody,
		TypeNode|null $errorType
	): AddOpenTypeNode {
		return new AddOpenTypeNode(
			$this->getSourceLocation(),
			$name,
			$valueType,
			$this->generateConstructorBody($constructorBody),
			$errorType
		);
	}

	public function addSealed(
		TypeNameIdentifier $name,
		TypeNode $valueType,
		ExpressionNode|null $constructorBody,
		TypeNode|null $errorType
	): AddSealedTypeNode {
		return new AddSealedTypeNode(
			$this->getSourceLocation(),
			$name,
			$valueType,
			$this->generateConstructorBody($constructorBody),
			$errorType
		);
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
	public function tupleType(array $types, ?TypeNode $restType = null): TupleTypeNode {
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

	public function byteArrayType(
		Number $minLength = new Number(0),
		PlusInfinity|Number $maxLength = PlusInfinity::value
	): ByteArrayTypeNode {
		return new ByteArrayTypeNode($this->getSourceLocation(), $minLength, $maxLength);
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

	public function integerValue(Number $value): IntegerValueNode {
		return new IntegerValueNode($this->getSourceLocation(), $value);
	}

	public function realValue(Number $value): RealValueNode {
		return new RealValueNode($this->getSourceLocation(), $value);
	}

	public function stringValue(string $value): StringValueNode {
		return new StringValueNode($this->getSourceLocation(), $value);
	}

	public function byteArrayValue(string $value): ByteArrayValueNode {
		return new ByteArrayValueNode($this->getSourceLocation(), $value);
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

	public function nameAndType(TypeNode $type, VariableNameIdentifier|null $name): NameAndTypeNode {
		return new NameAndTypeNode($this->getSourceLocation(), $type, $name);
	}


	/** @param array<VariableNameIdentifier> $variableNames */
	public function multiVariableAssignment(array $variableNames, ExpressionNode $assignedExpression): MultiVariableAssignmentExpressionNodeInterface {
		return new MultiVariableAssignmentExpressionNode(
			$this->sourceLocator->getSourceLocation(),
			$variableNames,
			$assignedExpression
		);
	}

}