<?php

namespace Walnut\Lang\Blueprint\Program\Builder;

use Walnut\Lang\Blueprint\AST\Node\Expression\ExpressionNode;
use Walnut\Lang\Blueprint\Function\FunctionBodyDraft;

interface FunctionBodyBuilder {
	public function functionBodyDraft(ExpressionNode $expressionNode): FunctionBodyDraft;
}