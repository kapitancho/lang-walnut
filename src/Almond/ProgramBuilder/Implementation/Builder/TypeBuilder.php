<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Builder;

use BcMath\Number;
use Exception;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\EnumerationValueNameNode;
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
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\IntegerValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\RealValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\StringValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Number\MinusInfinity as NodeMinusInfinity;
use Walnut\Lang\Almond\AST\Blueprint\Number\PlusInfinity as NodePlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Type\MetaTypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Type\UnknownType;
use Walnut\Lang\Almond\Engine\Implementation\Range\NumberInterval;
use Walnut\Lang\Almond\Engine\Implementation\Range\NumberIntervalEndpoint;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder\NameBuilder;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\CodeMapper;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder\TypeBuilder as TypeCompilerInterface;

final readonly class TypeBuilder implements TypeCompilerInterface {
	public function __construct(
		private NameBuilder        $nameBuilder,
		private TypeRegistry $typeRegistry,
		private CodeMapper $codeMapper,
	) {}

	/** @throws CompilationException */
	public function type(TypeNode $typeNode): Type {
		try {
			$result = match(true) {
				$typeNode instanceof AnyTypeNode => $this->typeRegistry->any,
				$typeNode instanceof NothingTypeNode => $this->typeRegistry->nothing,
				$typeNode instanceof TrueTypeNode => $this->typeRegistry->true,
				$typeNode instanceof FalseTypeNode => $this->typeRegistry->false,
				$typeNode instanceof BooleanTypeNode => $this->typeRegistry->boolean,
				$typeNode instanceof NullTypeNode => $this->typeRegistry->null,
				$typeNode instanceof UnionTypeNode => $this->typeRegistry->union([
					$this->type($typeNode->left),
					$this->type($typeNode->right)
				]),
				$typeNode instanceof IntersectionTypeNode => $this->typeRegistry->intersection([
					$this->type($typeNode->left),
					$this->type($typeNode->right)
				]),

				$typeNode instanceof ArrayTypeNode => $this->typeRegistry->array(
					$this->type($typeNode->itemType),
					$typeNode->minLength,
					$typeNode->maxLength === NodePlusInfinity::value ? PlusInfinity::value : $typeNode->maxLength
				),
				$typeNode instanceof MapTypeNode => $this->typeRegistry->map(
					$this->type($typeNode->itemType),
					$typeNode->minLength,
					$typeNode->maxLength === NodePlusInfinity::value ? PlusInfinity::value : $typeNode->maxLength,
					$this->type($typeNode->keyType),
				),
				$typeNode instanceof SetTypeNode => $this->typeRegistry->set(
					$this->type($typeNode->itemType),
					$typeNode->minLength,
					$typeNode->maxLength === NodePlusInfinity::value ? PlusInfinity::value : $typeNode->maxLength
				),
				$typeNode instanceof TupleTypeNode => $this->typeRegistry->tuple(
					array_map($this->type(...), $typeNode->types),
					$this->type($typeNode->restType)
				),
				$typeNode instanceof RecordTypeNode => $this->typeRegistry->record(
					array_map($this->type(...), $typeNode->types),
					$this->type($typeNode->restType)
				),
				$typeNode instanceof FunctionTypeNode => $this->typeRegistry->function(
					$this->type($typeNode->parameterType),
					$this->type($typeNode->returnType)
				),
				$typeNode instanceof ShapeTypeNode => $this->typeRegistry->shape(
					$this->type($typeNode->refType)
				),
				$typeNode instanceof TypeTypeNode => $this->typeRegistry->type(
					$this->type($typeNode->refType)
				),
				$typeNode instanceof ProxyTypeNode => $this->typeRegistry->proxy(
					$this->nameBuilder->typeName($typeNode->name)
				),
				$typeNode instanceof ImpureTypeNode => $this->typeRegistry->impure(
					$this->type($typeNode->valueType)
				),
				$typeNode instanceof OptionalKeyTypeNode => $this->typeRegistry->optionalKey(
					$this->type($typeNode->valueType)
				),
				$typeNode instanceof ResultTypeNode => $this->typeRegistry->result(
					$this->type($typeNode->returnType),
					$this->type($typeNode->errorType)
				),

				$typeNode instanceof MutableTypeNode => $this->typeRegistry->mutable(
					$this->type($typeNode->valueType)
				),

				$typeNode instanceof IntegerFullTypeNode => $this->typeRegistry->integerFull(...
					array_map(
						fn(NumberIntervalNode $interval) => new NumberInterval(
							$interval->start === NodeMinusInfinity::value ? MinusInfinity::value :
								new NumberIntervalEndpoint($interval->start->value, $interval->start->inclusive),
							$interval->end === NodePlusInfinity::value ? PlusInfinity::value :
								new NumberIntervalEndpoint($interval->end->value, $interval->end->inclusive),
						),
						$typeNode->intervals
					)
				),
				$typeNode instanceof IntegerTypeNode => $this->typeRegistry->integerFull(
					new NumberInterval(
						$typeNode->minValue === NodeMinusInfinity::value ? MinusInfinity::value :
							new NumberIntervalEndpoint($typeNode->minValue, true),
						$typeNode->maxValue === NodePlusInfinity::value ? PlusInfinity::value :
							new NumberIntervalEndpoint($typeNode->maxValue, true)
					)
					//$typeNode->minValue, $typeNode->maxValue
				),
				$typeNode instanceof IntegerSubsetTypeNode => $this->typeRegistry->integerSubset(
					array_map(fn(IntegerValueNode $stringValueNode): Number =>
					$stringValueNode->value, $typeNode->values)
				),
				$typeNode instanceof RealFullTypeNode => $this->typeRegistry->realFull(...
					array_map(
						fn(NumberIntervalNode $interval) => new NumberInterval(
							$interval->start === NodeMinusInfinity::value ? MinusInfinity::value :
								new NumberIntervalEndpoint($interval->start->value, $interval->start->inclusive),
							$interval->end === NodePlusInfinity::value ? PlusInfinity::value :
								new NumberIntervalEndpoint($interval->end->value, $interval->end->inclusive),
						),
						$typeNode->intervals
					)
				),
				$typeNode instanceof RealTypeNode => $this->typeRegistry->realFull(
					new NumberInterval(
						$typeNode->minValue === NodeMinusInfinity::value ? MinusInfinity::value :
							new NumberIntervalEndpoint($typeNode->minValue, true),
						$typeNode->maxValue === NodePlusInfinity::value ? PlusInfinity::value :
							new NumberIntervalEndpoint($typeNode->maxValue, true)
					)
				),
				$typeNode instanceof RealSubsetTypeNode => $this->typeRegistry->realSubset(
					array_map(fn(RealValueNode $stringValueNode): Number =>
						$stringValueNode->value, $typeNode->values)
				),
				$typeNode instanceof StringTypeNode => $this->typeRegistry->string(
					$typeNode->minLength,
					$typeNode->maxLength === NodePlusInfinity::value ? PlusInfinity::value : $typeNode->maxLength
				),
				$typeNode instanceof StringSubsetTypeNode => $this->typeRegistry->stringSubset(
					array_map(fn(StringValueNode $stringValueNode): string =>
						$stringValueNode->value, $typeNode->values)
				),

				$typeNode instanceof BytesTypeNode => $this->typeRegistry->bytes(
					$typeNode->minLength,
					$typeNode->maxLength === NodePlusInfinity::value ? PlusInfinity::value : $typeNode->maxLength
				),

				$typeNode instanceof MetaTypeTypeNode => $this->typeRegistry->metaType(
					MetaTypeValue::from($typeNode->value)
				),
				$typeNode instanceof NamedTypeNode => $this->typeRegistry->typeByName($this->nameBuilder->typeName($typeNode->name)),
				$typeNode instanceof EnumerationSubsetTypeNode =>
					$this->typeRegistry->userland->enumerationSubsetType(
						$this->nameBuilder->typeName($typeNode->name),
						array_map(
							fn(EnumerationValueNameNode $value): EnumerationValueName =>
								$this->nameBuilder->enumerationValueName($value),
							$typeNode->values
						)
					),

				// @codeCoverageIgnoreStart
				true => throw new CompilationException(
					$typeNode,
					"Unknown type node type: " . get_class($typeNode)
				)
				// @codeCoverageIgnoreEnd
			};
			if ($result === UnknownType::value) {
				//TODO: better exception
				throw new Exception("The type could not be resolved: ". json_encode($typeNode));
			}
			$this->codeMapper->mapNode($typeNode, $result);
			return $result;
		} catch (UnknownType|DuplicateSubsetValue|InvalidLengthRange|InvalidNumberInterval|InvalidMapKeyType|UnknownEnumerationValue $e) {
			throw new CompilationException($typeNode, $e->getMessage(), $e);
		}
	}

}