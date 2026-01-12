<?php

namespace Walnut\Lang\Blueprint\Compilation\AST;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;
use Walnut\Lang\Blueprint\AST\Node\SourceNode;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Function\CustomMethod;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;

interface AstSourceLocator {
	public function getSourceNode(Expression|Value|Type|FunctionBody|CustomMethod $element): SourceNode|null;
	public function getSourceLocation(Expression|Value|Type|FunctionBody|CustomMethod $element): SourceLocation|null;
}