<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\CodeMapper;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceNode;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\FunctionBody;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\UserlandFunction;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland\UserlandMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\VariableName;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\CodeMapper;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\SourceNodeLocator;

final readonly class NoopCodeMapper implements CodeMapper, SourceNodeLocator {
	public function mapNode(
		SourceNode $node,
		Expression|Value|Type|FunctionBody|UserlandMethod|TypeName|VariableName|MethodName|EnumerationValueName $element
	): void {}

	public function getSourceNode(Expression|Value|Type|UserlandMethod|UserlandFunction|FunctionBody $element): SourceNode|null {
		return null;
	}

	public function getSourceLocation(Expression|Value|Type|UserlandMethod|UserlandFunction|FunctionBody $element): SourceLocation|null {
		return null;
	}
}