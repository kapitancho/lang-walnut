<?php

namespace Walnut\Lang\Implementation\Compilation;

use BcMath\Number;
use Exception;
use Walnut\Lang\Blueprint\AST\Compiler\AstCompilationException;
use Walnut\Lang\Blueprint\AST\Compiler\AstCompiler as AstCompilerInterface;
use Walnut\Lang\Blueprint\AST\Compiler\AstModuleCompilationException;
use Walnut\Lang\Blueprint\AST\Compiler\AstProgramCompilationException;
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
use Walnut\Lang\Blueprint\AST\Node\Module\ModuleDefinitionNode;
use Walnut\Lang\Blueprint\AST\Node\Module\ModuleNode;
use Walnut\Lang\Blueprint\AST\Node\RootNode;
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
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpressionDefault;
use Walnut\Lang\Blueprint\Code\Expression\MatchExpressionPair;
use Walnut\Lang\Blueprint\Common\Range\InvalidIntegerRange;
use Walnut\Lang\Blueprint\Common\Range\InvalidLengthRange;
use Walnut\Lang\Blueprint\Common\Range\InvalidRealRange;
use Walnut\Lang\Blueprint\Compilation\CodeBuilder;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Blueprint\Program\UnknownType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\UnknownEnumerationValue;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;

final readonly class AstCompiler implements AstCompilerInterface {
	public function __construct(
		private readonly CodeBuilder $codeBuilder,
	) {}

	/** @throws AstProgramCompilationException */
	public function compile(RootNode $root): void {
		$exceptions = array();
		array_map(function(ModuleNode $module) use (&$exceptions) {
			try {
				$this->compileModule($module);
			} catch (AstModuleCompilationException $e) {
				$exceptions[] = $e;
			}
		}, $root->modules);

		if (count($exceptions) > 0) {
			throw new AstProgramCompilationException($exceptions);
		}
	}

	/** @throws AstModuleCompilationException */
	private function compileModule(ModuleNode $module): void {
		$exceptions = array();
		array_map(function(ModuleDefinitionNode $moduleDefinition) use (&$exceptions) {
			try {
				$this->compileModuleDefinition($moduleDefinition);
			} catch (AstCompilationException $e) {
				$exceptions[] = $e;
			}
		}, $module->definitions);

		if (count($exceptions) > 0) {
			throw new AstModuleCompilationException($module->moduleName, $exceptions);
		}
	}

	/** @throws AstCompilationException */
	private function compileModuleDefinition(ModuleDefinitionNode $moduleDefinition): void {
		match(true) {
			$moduleDefinition instanceof AddAliasTypeNode =>
				$this->codeBuilder->addAlias(
					$moduleDefinition->name,
					$this->type($moduleDefinition->aliasedType)
				),
			$moduleDefinition instanceof AddAtomTypeNode =>
				$this->codeBuilder->addAtom($moduleDefinition->name),
			$moduleDefinition instanceof AddConstructorMethodNode =>
				$this->codeBuilder->addConstructorMethod(
					$moduleDefinition->typeName,
					$this->type($moduleDefinition->parameterType),
					$this->type($moduleDefinition->dependencyType),
					$this->type($moduleDefinition->errorType),
					$this->functionBody($moduleDefinition->functionBody)
				),
			$moduleDefinition instanceof AddEnumerationTypeNode =>
				$this->codeBuilder->addEnumeration(
					$moduleDefinition->name,
					$moduleDefinition->values
				),
			$moduleDefinition instanceof AddMethodNode =>
				$this->codeBuilder->addMethod(
					$this->type($moduleDefinition->targetType),
					$moduleDefinition->methodName,
					$this->type($moduleDefinition->parameterType),
					$this->type($moduleDefinition->dependencyType),
					$this->type($moduleDefinition->returnType),
					$this->functionBody($moduleDefinition->functionBody)
				),
			$moduleDefinition instanceof AddSealedTypeNode =>
				$this->codeBuilder->addSealed(
					$moduleDefinition->name,
					$this->type($moduleDefinition->valueType),
					$this->expression($moduleDefinition->constructorBody),
					$this->type($moduleDefinition->errorType)
				),
			$moduleDefinition instanceof AddSubtypeTypeNode =>
				$this->codeBuilder->addSubtype(
					$moduleDefinition->name,
					$this->type($moduleDefinition->baseType),
					$this->expression($moduleDefinition->constructorBody),
					$this->type($moduleDefinition->errorType)
				),
			$moduleDefinition instanceof AddVariableNode =>
				$this->codeBuilder->addVariable($moduleDefinition->name,
					$this->value($moduleDefinition->value)),

			true => throw new AstCompilationException(
				$moduleDefinition,
				"Unknown module definition node type: " . get_class($moduleDefinition)
			)
		};
	}

	/** @throws Exception */
	private function matchExpressionPair(MatchExpressionPairNode $matchExpressionPairNode): MatchExpressionPair {
		return $this->codeBuilder->matchPair(
			$this->expression($matchExpressionPairNode->matchExpression),
			$this->expression($matchExpressionPairNode->valueExpression)
		);
	}

	/** @throws AstCompilationException */
	private function matchExpressionDefault(MatchExpressionDefaultNode $matchExpressionDefaultNode): MatchExpressionDefault {
		return $this->codeBuilder->matchDefault(
			$this->expression($matchExpressionDefaultNode->valueExpression)
		);
	}

	/** @throws AstCompilationException */
	private function matchExpression(MatchExpressionPairNode|MatchExpressionDefaultNode $matchExpressionNode): MatchExpressionPair|MatchExpressionDefault {
		return match(true) {
			$matchExpressionNode instanceof MatchExpressionPairNode =>
				$this->matchExpressionPair($matchExpressionNode),
			$matchExpressionNode instanceof MatchExpressionDefaultNode =>
				$this->matchExpressionDefault($matchExpressionNode),
		};
	}

	/** @throws AstCompilationException */
	private function expression(ExpressionNode $expressionNode): Expression {
		return match(true) {
			$expressionNode instanceof ConstantExpressionNode =>
				$this->codeBuilder->constant(
					$this->value($expressionNode->value)
				),
			$expressionNode instanceof ConstructorCallExpressionNode =>
				$this->codeBuilder->constructorCall(
					$expressionNode->typeName,
					$this->expression($expressionNode->parameter)
				),
			$expressionNode instanceof FunctionCallExpressionNode =>
				$this->codeBuilder->functionCall(
					$this->expression($expressionNode->target),
					$this->expression($expressionNode->parameter)
				),
			/*$expressionNode instanceof MatchExpressionDefaultNode =>
				$this->codeBuilder->matchDefault(
					$this->expression($expressionNode->valueExpression)
				),
			$expressionNode instanceof MatchExpressionPairNode =>
				$this->codeBuilder->matchPair(
					$this->expression($expressionNode->matchExpression),
					$this->expression($expressionNode->valueExpression)
				),*/
			$expressionNode instanceof MatchIfExpressionNode =>
				$this->codeBuilder->matchIf(
					$this->expression($expressionNode->condition),
					$this->expression($expressionNode->then),
					$this->expression($expressionNode->else)
				),
			$expressionNode instanceof MatchTrueExpressionNode =>
				$this->codeBuilder->matchTrue(
					array_map($this->matchExpression(...), $expressionNode->pairs)
				),
			$expressionNode instanceof MatchTypeExpressionNode =>
				$this->codeBuilder->matchType(
					$this->expression($expressionNode->target),
					array_map($this->matchExpression(...), $expressionNode->pairs)
				),
			$expressionNode instanceof MatchValueExpressionNode =>
				$this->codeBuilder->matchValue(
					$this->expression($expressionNode->target),
					array_map($this->matchExpression(...), $expressionNode->pairs)
				),
			$expressionNode instanceof MethodCallExpressionNode =>
				$this->codeBuilder->methodCall(
					$this->expression($expressionNode->target),
					$expressionNode->methodName,
					$this->expression($expressionNode->parameter)
				),
			$expressionNode instanceof MutableExpressionNode =>
				$this->codeBuilder->mutable(
					$this->type($expressionNode->type),
					$this->expression($expressionNode->value),
				),
			$expressionNode instanceof NoErrorExpressionNode =>
				$this->codeBuilder->noError(
					$this->expression($expressionNode->targetExpression),
				),
			$expressionNode instanceof NoExternalErrorExpressionNode =>
				$this->codeBuilder->noExternalError(
					$this->expression($expressionNode->targetExpression),
				),
			$expressionNode instanceof PropertyAccessExpressionNode =>
				$this->codeBuilder->propertyAccess(
					$this->expression($expressionNode->target),
					$expressionNode->propertyName
				),
			$expressionNode instanceof RecordExpressionNode =>
				$this->codeBuilder->record(
					array_map($this->expression(...), $expressionNode->values)
				),
			$expressionNode instanceof ReturnExpressionNode =>
				$this->codeBuilder->return(
					$this->expression($expressionNode->returnedExpression)
				),
			$expressionNode instanceof SequenceExpressionNode =>
				$this->codeBuilder->sequence(
					array_map($this->expression(...), $expressionNode->expressions)
				),
			$expressionNode instanceof TupleExpressionNode =>
				$this->codeBuilder->tuple(
					array_map($this->expression(...), $expressionNode->values)
				),
			$expressionNode instanceof VariableAssignmentExpressionNode =>
				$this->codeBuilder->variableAssignment(
					$expressionNode->variableName,
					$this->expression($expressionNode->assignedExpression)
				),
			$expressionNode instanceof VariableNameExpressionNode =>
				$this->codeBuilder->variableName(
					$expressionNode->variableName
				),
			true => throw new AstCompilationException(
				$expressionNode,
				"Unknown expression node type: " . get_class($expressionNode)
			)
		};
	}

	/** @throws AstCompilationException */
	private function type(TypeNode $typeNode): Type {
		try {
			return match(true) {
				$typeNode instanceof AnyTypeNode => $this->codeBuilder->typeRegistry->any,
				$typeNode instanceof NothingTypeNode => $this->codeBuilder->typeRegistry->nothing,
				$typeNode instanceof TrueTypeNode => $this->codeBuilder->typeRegistry->true,
				$typeNode instanceof FalseTypeNode => $this->codeBuilder->typeRegistry->false,
				$typeNode instanceof BooleanTypeNode => $this->codeBuilder->typeRegistry->boolean,
				$typeNode instanceof NullTypeNode => $this->codeBuilder->typeRegistry->null,
				$typeNode instanceof UnionTypeNode => $this->codeBuilder->typeRegistry->union([
					$this->type($typeNode->left),
					$this->type($typeNode->right)
				]),
				$typeNode instanceof IntersectionTypeNode => $this->codeBuilder->typeRegistry->intersection([
					$this->type($typeNode->left),
					$this->type($typeNode->right)
				]),

				$typeNode instanceof ArrayTypeNode => $this->codeBuilder->typeRegistry->array(
					$this->type($typeNode->itemType),
					$typeNode->minLength,
					$typeNode->maxLength
				),
				$typeNode instanceof MapTypeNode => $this->codeBuilder->typeRegistry->map(
					$this->type($typeNode->itemType),
					$typeNode->minLength,
					$typeNode->maxLength
				),
				$typeNode instanceof TupleTypeNode => $this->codeBuilder->typeRegistry->tuple(
					array_map($this->type(...), $typeNode->types),
					$this->type($typeNode->restType)
				),
				$typeNode instanceof RecordTypeNode => $this->codeBuilder->typeRegistry->record(
					array_map($this->type(...), $typeNode->types),
					$this->type($typeNode->restType)
				),
				$typeNode instanceof FunctionTypeNode => $this->codeBuilder->typeRegistry->function(
					$this->type($typeNode->parameterType),
					$this->type($typeNode->returnType)
				),
				$typeNode instanceof TypeTypeNode => $this->codeBuilder->typeRegistry->type(
					$this->type($typeNode->refType)
				),
				$typeNode instanceof ProxyTypeNode => $this->codeBuilder->typeRegistry->proxyType($typeNode->name),
				$typeNode instanceof ImpureTypeNode => $this->codeBuilder->typeRegistry->impure(
					$this->type($typeNode->valueType)
				),
				$typeNode instanceof OptionalKeyTypeNode => $this->codeBuilder->typeRegistry->optionalKey(
					$this->type($typeNode->valueType)
				),
				$typeNode instanceof ResultTypeNode => $this->codeBuilder->typeRegistry->result(
					$this->type($typeNode->returnType),
					$this->type($typeNode->errorType)
				),

				$typeNode instanceof MutableTypeNode => $this->codeBuilder->typeRegistry->mutable(
					$this->type($typeNode->valueType)
				),

				$typeNode instanceof IntegerTypeNode => $this->codeBuilder->typeRegistry->integer(
					$typeNode->minValue, $typeNode->maxValue
				),
				$typeNode instanceof IntegerSubsetTypeNode => $this->codeBuilder->typeRegistry->integerSubset(
					array_map(
						fn(Number $value): IntegerValue
							=> $this->codeBuilder->valueRegistry->integer($value),
						$typeNode->values
					)
				),
				$typeNode instanceof RealTypeNode => $this->codeBuilder->typeRegistry->real(
					$typeNode->minValue, $typeNode->maxValue
				),
				$typeNode instanceof RealSubsetTypeNode => $this->codeBuilder->typeRegistry->realSubset(
					array_map(
						fn(Number $value): RealValue
							=> $this->codeBuilder->valueRegistry->real($value),
						$typeNode->values
					)
				),
				$typeNode instanceof StringTypeNode => $this->codeBuilder->typeRegistry->string(
					$typeNode->minLength, $typeNode->maxLength
				),
				$typeNode instanceof StringSubsetTypeNode => $this->codeBuilder->typeRegistry->stringSubset(
					array_map(
						fn(string $value): StringValue
							=> $this->codeBuilder->valueRegistry->string($value),
						$typeNode->values
					)
				),

				$typeNode instanceof MetaTypeTypeNode => $this->codeBuilder->typeRegistry->metaType($typeNode->value),
				$typeNode instanceof NamedTypeNode => $this->codeBuilder->typeRegistry->typeByName($typeNode->name),
				$typeNode instanceof EnumerationSubsetTypeNode =>
					$this->codeBuilder->typeRegistry->enumeration($typeNode->name)->subsetType($typeNode->values),

				true => throw new AstCompilationException(
					$typeNode,
					"Unknown type node type: " . get_class($typeNode)
				)
			};
		} catch (UnknownType $e) {
			throw new AstCompilationException($typeNode, "Type issue: " . $e->getMessage(), $e);
		} catch (UnknownEnumerationValue $e) {
			throw new AstCompilationException($typeNode, "Enumeration issue: " . $e->getMessage(), $e);
		} catch (InvalidIntegerRange|InvalidRealRange|InvalidLengthRange $e) {
			throw new AstCompilationException($typeNode, "Range issue: " . $e->getMessage(), $e);
		}
	}

	/** @throws AstCompilationException */
	private function value(ValueNode $valueNode): Value {
		try {
			return match(true) {
				$valueNode instanceof NullValueNode => $this->codeBuilder->valueRegistry->null,
				$valueNode instanceof TrueValueNode => $this->codeBuilder->valueRegistry->true,
				$valueNode instanceof FalseValueNode => $this->codeBuilder->valueRegistry->false,
				//$valueNode instanceof ErrorValueNode => $this->codeBuilder->valueRegistry->error($valueNode->value),
				//$valueNode instanceof MutableValueNode => $this->codeBuilder->valueRegistry->mutable($valueNode->value),
				$valueNode instanceof IntegerValueNode => $this->codeBuilder->valueRegistry->integer($valueNode->value),
				$valueNode instanceof RealValueNode => $this->codeBuilder->valueRegistry->real($valueNode->value),
				$valueNode instanceof StringValueNode => $this->codeBuilder->valueRegistry->string($valueNode->value),
				$valueNode instanceof AtomValueNode => $this->codeBuilder->valueRegistry->atom($valueNode->name),
				$valueNode instanceof EnumerationValueNode => $this->codeBuilder->valueRegistry->enumerationValue(
					$valueNode->name,
					$valueNode->enumValue
				),
				$valueNode instanceof RecordValueNode => $this->codeBuilder->valueRegistry->record(
					array_map($this->value(...), $valueNode->values)
				),
				$valueNode instanceof TupleValueNode => $this->codeBuilder->valueRegistry->tuple(
					array_map($this->value(...), $valueNode->values)
				),
				$valueNode instanceof TypeValueNode => $this->codeBuilder->valueRegistry->type(
					$this->type($valueNode->type)
				),
				$valueNode instanceof FunctionValueNode =>
					$this->codeBuilder->valueRegistry->function(
						$this->type($valueNode->parameterType),
						$this->type($valueNode->dependencyType),
						$this->type($valueNode->returnType),
						$this->functionBody($valueNode->functionBody)
					),
				true => throw new AstCompilationException(
					$valueNode,
					"Unknown value node type: " . get_class($valueNode)
				)
			};
		} catch (UnknownType $e) {
			throw new AstCompilationException($valueNode, "Type issue: " . $e->getMessage(), $e);
		} catch (UnknownEnumerationValue $e) {
			throw new AstCompilationException($valueNode, "Enumeration Issue: " . $e->getMessage(), $e);
		}
	}

	/** @throws AstCompilationException */
	private function functionBody(FunctionBodyNode $functionBodyNode): FunctionBody {
		return $this->codeBuilder->functionBody(
			$this->expression($functionBodyNode->expression)
		);
	}

}