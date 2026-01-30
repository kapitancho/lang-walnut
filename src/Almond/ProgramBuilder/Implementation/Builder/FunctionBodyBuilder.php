<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Builder;

use Walnut\Lang\Almond\AST\Blueprint\Node\FunctionBodyNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Name\VariableNameNode;
use Walnut\Lang\Almond\Engine\Blueprint\Function\FunctionBody;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\ExpressionRegistry;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder\NameBuilder;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\CodeMapper;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder\ExpressionBuilder;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder\FunctionBodyBuilder as FunctionBodyCompilerInterface;

final readonly class FunctionBodyBuilder implements FunctionBodyCompilerInterface {

	public function __construct(
		private NameBuilder        $nameBuilder,
		private ExpressionBuilder  $astExpressionCompiler,
		private ExpressionRegistry $expressionRegistry,
		private CodeMapper         $codeMapper,
	) {}

	/** @throws CompilationException */
	public function functionBody(FunctionBodyNode $functionBodyNode): FunctionBody {
		$result = $this->expressionRegistry->functionBody(
			$this->astExpressionCompiler->expression($functionBodyNode->expression)
		);
		$this->codeMapper->mapNode($functionBodyNode, $result);
		return $result;
	}

	/** @throws CompilationException */
	public function validatorBody(
		FunctionBodyNode $functionBodyNode
	): FunctionBody {
		return $this->expressionRegistry->functionBody(
			$this->expressionRegistry->sequence([
				$this->astExpressionCompiler->expression($functionBodyNode->expression),
				$this->expressionRegistry->variableName(
					new VariableName('#')
				)
			])
		);
	}

}