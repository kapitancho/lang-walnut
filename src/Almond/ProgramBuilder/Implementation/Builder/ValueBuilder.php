<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Builder;

use Walnut\Lang\Almond\AST\Blueprint\Node\Type\TypeNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\AtomValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\BytesValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\DataValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\EnumerationValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\ErrorValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\FalseValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\FunctionValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\IntegerValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\MutableValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\NullValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\RealValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\RecordValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\SetValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\StringValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\TrueValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\TupleValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\TypeValueNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Value\ValueNode;
use Walnut\Lang\Almond\Engine\Blueprint\Abc\Function\FunctionValueFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Type\P\NameAndType;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder\FunctionBodyBuilder;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder\NameBuilder;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder\TypeBuilder;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder\ValueBuilder as ValueCompilerInterface;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\CodeMapper;

final readonly class ValueBuilder implements ValueCompilerInterface {
	public function __construct(
		private NameBuilder        $nameBuilder,
		private TypeRegistry        $typeRegistry,
		private ValueRegistry       $valueRegistry,
		private FunctionValueFactory $functionValueFactory,
		private FunctionBodyBuilder $functionBodyCompiler,
		private TypeBuilder         $typeBuilder,
		private CodeMapper          $codeMapper,
	) {}

	/** @throws CompilationException */
	public function type(TypeNode $typeNode): Type {
		return $this->typeBuilder->type($typeNode);
	}

	/** @throws CompilationException */
	public function value(ValueNode $valueNode): Value {
		try {
			$result = match(true) {
				$valueNode instanceof NullValueNode => $this->valueRegistry->null,
				$valueNode instanceof TrueValueNode => $this->valueRegistry->true,
				$valueNode instanceof FalseValueNode => $this->valueRegistry->false,
				$valueNode instanceof IntegerValueNode => $this->valueRegistry->integer($valueNode->value),
				$valueNode instanceof RealValueNode => $this->valueRegistry->real($valueNode->value),
				$valueNode instanceof StringValueNode => $this->valueRegistry->string($valueNode->value),
				$valueNode instanceof BytesValueNode => $this->valueRegistry->bytes($valueNode->value),
				$valueNode instanceof MutableValueNode => $this->valueRegistry->mutable(
					$this->type($valueNode->type),
					$this->value($valueNode->value),
				),
				$valueNode instanceof ErrorValueNode => $this->valueRegistry->error(
					$this->value($valueNode->value),
				),
				$valueNode instanceof AtomValueNode => $this->valueRegistry->atom(
					$this->nameBuilder->typeName($valueNode->name)
				),
				$valueNode instanceof DataValueNode => $this->valueRegistry->data(
					$this->nameBuilder->typeName($valueNode->name),
					$this->value($valueNode->value),
				),
				$valueNode instanceof EnumerationValueNode => $this->valueRegistry->enumeration(
					$this->nameBuilder->typeName($valueNode->name),
					$this->nameBuilder->enumerationValueName($valueNode->enumValue)
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
					$this->functionValueFactory->function(
						new NameAndType(
							$this->type($valueNode->parameter->type),
							$valueNode->parameter->name ?
								$this->nameBuilder->variableName($valueNode->parameter->name) : null
						),
						new NameAndType(
							$this->type($valueNode->dependency->type),
							$valueNode->dependency->name ?
								$this->nameBuilder->variableName($valueNode->dependency->name) : null
						),
						$this->type($valueNode->returnType),
						$this->functionBodyCompiler->functionBody($valueNode->functionBody),
					),
				// @codeCoverageIgnoreStart
				true => throw new CompilationException(
					$valueNode,
					"Unknown value node type: " . get_class($valueNode)
				)
				// @codeCoverageIgnoreEnd
			};
			$this->codeMapper->mapNode($valueNode, $result);
			return $result;
		} catch (UnknownType|UnknownEnumerationValue $e) {
			throw new CompilationException($valueNode, $e->getMessage(), $e);
		}
	}

}