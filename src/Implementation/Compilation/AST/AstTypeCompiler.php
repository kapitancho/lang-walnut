<?php

namespace Walnut\Lang\Implementation\Compilation\AST;

use Walnut\Lang\Blueprint\AST\Node\Type\AnyTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\ArrayTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\BooleanTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Type\ByteArrayTypeNode;
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
use Walnut\Lang\Blueprint\Common\Range\InvalidLengthRange;
use Walnut\Lang\Blueprint\Common\Range\InvalidNumberInterval;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Compilation\AST\AstCodeMapper;
use Walnut\Lang\Blueprint\Compilation\AST\AstCompilationException;
use Walnut\Lang\Blueprint\Compilation\AST\AstTypeCompiler as AstTypeCompilerInterface;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\UnknownType;
use Walnut\Lang\Blueprint\Type\DuplicateSubsetValue;
use Walnut\Lang\Blueprint\Type\InvalidMapKeyType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\UnknownEnumerationValue;
use Walnut\Lang\Implementation\Common\Range\NumberInterval;
use Walnut\Lang\Implementation\Common\Range\NumberIntervalEndpoint;

final readonly class AstTypeCompiler implements AstTypeCompilerInterface {
	public function __construct(
		private TypeRegistry $typeRegistry,
		private AstCodeMapper $astCodeMapper,
	) {}

	/** @throws AstCompilationException */
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
					$typeNode->maxLength
				),
				$typeNode instanceof MapTypeNode => $this->typeRegistry->map(
					$this->type($typeNode->itemType),
					$typeNode->minLength,
					$typeNode->maxLength,
					$this->type($typeNode->keyType),
				),
				$typeNode instanceof SetTypeNode => $this->typeRegistry->set(
					$this->type($typeNode->itemType),
					$typeNode->minLength,
					$typeNode->maxLength
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
				$typeNode instanceof ProxyTypeNode => $this->typeRegistry->proxyType($typeNode->name),
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
							$interval->start,
							$interval->end
						),
						$typeNode->intervals
					)
				),
				$typeNode instanceof IntegerTypeNode => $this->typeRegistry->integerFull(
					new NumberInterval(
						$typeNode->minValue === MinusInfinity::value ? MinusInfinity::value :
							new NumberIntervalEndpoint($typeNode->minValue, true),
						$typeNode->maxValue === PlusInfinity::value ? PlusInfinity::value :
							new NumberIntervalEndpoint($typeNode->maxValue, true)
					)
					//$typeNode->minValue, $typeNode->maxValue
				),
				$typeNode instanceof IntegerSubsetTypeNode => $this->typeRegistry->integerSubset($typeNode->values),
				$typeNode instanceof RealFullTypeNode => $this->typeRegistry->realFull(...
					array_map(
						fn(NumberIntervalNode $interval) => new NumberInterval(
							$interval->start,
							$interval->end
						),
						$typeNode->intervals
					)
				),
				$typeNode instanceof RealTypeNode => $this->typeRegistry->realFull(
					new NumberInterval(
						$typeNode->minValue === MinusInfinity::value ? MinusInfinity::value :
							new NumberIntervalEndpoint($typeNode->minValue, true),
						$typeNode->maxValue === PlusInfinity::value ? PlusInfinity::value :
							new NumberIntervalEndpoint($typeNode->maxValue, true)
					)
				),
				$typeNode instanceof RealSubsetTypeNode => $this->typeRegistry->realSubset($typeNode->values),
				$typeNode instanceof StringTypeNode => $this->typeRegistry->string(
					$typeNode->minLength, $typeNode->maxLength
				),
				$typeNode instanceof StringSubsetTypeNode => $this->typeRegistry->stringSubset($typeNode->values),

				$typeNode instanceof ByteArrayTypeNode => $this->typeRegistry->byteArray(
					$typeNode->minLength, $typeNode->maxLength
				),

				$typeNode instanceof MetaTypeTypeNode => $this->typeRegistry->metaType($typeNode->value),
				$typeNode instanceof NamedTypeNode => $this->typeRegistry->typeByName($typeNode->name),
				$typeNode instanceof EnumerationSubsetTypeNode =>
					$this->typeRegistry->enumerationSubsetType($typeNode->name, $typeNode->values),

				// @codeCoverageIgnoreStart
				true => throw new AstCompilationException(
					$typeNode,
					"Unknown type node type: " . get_class($typeNode)
				)
				// @codeCoverageIgnoreEnd
			};
			$this->astCodeMapper->mapNode($typeNode, $result);
			return $result;
		} catch (UnknownType $e) {
			throw new AstCompilationException($typeNode, "Type issue: " . $e->getMessage(), $e);
		} catch (DuplicateSubsetValue $e) {
			throw new AstCompilationException($typeNode, "Duplication issue: " . $e->getMessage(), $e);
		} catch (InvalidMapKeyType $e) {
			throw new AstCompilationException($typeNode, "Map key type issue: " . $e->getMessage(), $e);
		} catch (UnknownEnumerationValue $e) {
			throw new AstCompilationException($typeNode, "Enumeration issue: " . $e->getMessage(), $e);
		} catch (InvalidLengthRange|InvalidNumberInterval $e) {
			throw new AstCompilationException($typeNode, "Range issue: " . $e->getMessage(), $e);
		}
	}

}