<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Blueprint;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceNode;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\FunctionBody;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\UserlandFunction;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland\UserlandMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;

interface SourceNodeLocator {
	public function getSourceNode(Expression|Value|Type|FunctionBody|UserlandMethod|UserlandFunction $element): SourceNode|null;
	public function getSourceLocation(Expression|Value|Type|FunctionBody|UserlandMethod|UserlandFunction $element): SourceLocation|null;
}