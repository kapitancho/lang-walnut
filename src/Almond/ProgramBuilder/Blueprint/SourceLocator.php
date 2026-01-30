<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Blueprint;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceNode;
use Walnut\Lang\Almond\Engine\Blueprint\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Function\FunctionBody;
use Walnut\Lang\Almond\Engine\Blueprint\Function\UserlandFunction;
use Walnut\Lang\Almond\Engine\Blueprint\Method\UserlandMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

interface SourceLocator {
	public function getSourceNode(Expression|Value|Type|FunctionBody|UserlandMethod|UserlandFunction $element): SourceNode|null;
	public function getSourceLocation(Expression|Value|Type|FunctionBody|UserlandMethod|UserlandFunction $element): SourceLocation|null;
}