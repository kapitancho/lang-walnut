<?php

namespace Walnut\Lang\Implementation\Compilation\AST;

use Walnut\Lang\Blueprint\AST\Node\Type\TypeNode;
use Walnut\Lang\Blueprint\AST\Node\Value\AtomValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\DataValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\EnumerationValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\ErrorValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\FalseValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\FunctionValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\IntegerValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\MutableValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\NullValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\RealValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\RecordValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\SetValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\StringValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\TrueValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\TupleValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\TypeValueNode;
use Walnut\Lang\Blueprint\AST\Node\Value\ValueNode;
use Walnut\Lang\Blueprint\Compilation\AST\AstCodeMapper;
use Walnut\Lang\Blueprint\Compilation\AST\AstCompilationException;
use Walnut\Lang\Blueprint\Compilation\AST\AstFunctionBodyCompiler;
use Walnut\Lang\Blueprint\Compilation\AST\AstTypeCompiler;
use Walnut\Lang\Blueprint\Compilation\AST\AstValueCompiler as AstValueCompilerInterface;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Blueprint\Program\UnknownType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\UnknownEnumerationValue;
use Walnut\Lang\Blueprint\Value\Value;

final readonly class AstValueCompiler implements AstValueCompilerInterface {
	public function __construct(
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
		private AstFunctionBodyCompiler $functionBodyCompiler,
		private AstTypeCompiler $astTypeCompiler,
		private AstCodeMapper $astCodeMapper,
	) {}

	/** @throws AstCompilationException */
	public function type(TypeNode $typeNode): Type {
		return $this->astTypeCompiler->type($typeNode);
	}

	/** @throws AstCompilationException */
	public function value(ValueNode $valueNode): Value {
		try {
			$result = match(true) {
				$valueNode instanceof NullValueNode => $this->valueRegistry->null,
				$valueNode instanceof TrueValueNode => $this->valueRegistry->true,
				$valueNode instanceof FalseValueNode => $this->valueRegistry->false,
				$valueNode instanceof IntegerValueNode => $this->valueRegistry->integer($valueNode->value),
				$valueNode instanceof RealValueNode => $this->valueRegistry->real($valueNode->value),
				$valueNode instanceof StringValueNode => $this->valueRegistry->string($valueNode->value),
				$valueNode instanceof MutableValueNode => $this->valueRegistry->mutable(
					$this->type($valueNode->type),
					$this->value($valueNode->value),
				),
				$valueNode instanceof ErrorValueNode => $this->valueRegistry->error(
					$this->value($valueNode->value),
				),
				$valueNode instanceof AtomValueNode => $this->valueRegistry->atom($valueNode->name),
				$valueNode instanceof DataValueNode => $this->valueRegistry->dataValue(
					$valueNode->name,
					$this->value($valueNode->value),
				),
				$valueNode instanceof EnumerationValueNode => $this->valueRegistry->enumerationValue(
					$valueNode->name,
					$valueNode->enumValue
				),
				$valueNode instanceof RecordValueNode => $this->valueRegistry->record(
					array_map($this->value(...), $valueNode->values)
				),
				$valueNode instanceof TupleValueNode => $this->valueRegistry->tuple(
					array_map($this->value(...), $valueNode->values)
				),
				$valueNode instanceof SetValueNode => $this->valueRegistry->set(
					array_map($this->value(...), $valueNode->values)
				),
				$valueNode instanceof TypeValueNode => $this->valueRegistry->type(
					$this->type($valueNode->type)
				),
				$valueNode instanceof FunctionValueNode =>
					$this->valueRegistry->function(
						$this->typeRegistry->nameAndType(
							$this->type($valueNode->parameter->type),
							$valueNode->parameter->name
						),
						$this->typeRegistry->nameAndType(
							$this->type($valueNode->dependency->type),
							$valueNode->dependency->name
						),
						$this->type($valueNode->returnType),
						$this->functionBodyCompiler->functionBody($valueNode->functionBody),
						(string)$valueNode->sourceLocation
					),
				// @codeCoverageIgnoreStart
				true => throw new AstCompilationException(
					$valueNode,
					"Unknown value node type: " . get_class($valueNode)
				)
				// @codeCoverageIgnoreEnd
			};
			$this->astCodeMapper->mapNode($valueNode, $result);
			return $result;
		} catch (UnknownType $e) {
			throw new AstCompilationException($valueNode, "Type issue: " . $e->getMessage(), $e);
		} catch (UnknownEnumerationValue $e) {
			throw new AstCompilationException($valueNode, "Enumeration Issue: " . $e->getMessage(), $e);
		}
	}

}