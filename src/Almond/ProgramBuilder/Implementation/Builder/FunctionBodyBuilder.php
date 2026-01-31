<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Builder;

use Walnut\Lang\Almond\AST\Blueprint\Node\FunctionBodyNode;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\ExpressionRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\FunctionBody;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder\ExpressionBuilder;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder\FunctionBodyBuilder as FunctionBodyCompilerInterface;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\BuildException;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\CodeMapper;

final readonly class FunctionBodyBuilder implements FunctionBodyCompilerInterface {

	public function __construct(
		private ExpressionBuilder  $astExpressionCompiler,
		private ExpressionRegistry $expressionRegistry,
		private CodeMapper         $codeMapper,
	) {}

	/** @throws BuildException */
	public function functionBody(FunctionBodyNode $functionBodyNode): FunctionBody {
		$result = $this->expressionRegistry->functionBody(
			$this->astExpressionCompiler->expression($functionBodyNode->expression)
		);
		$this->codeMapper->mapNode($functionBodyNode, $result);
		return $result;
	}

}