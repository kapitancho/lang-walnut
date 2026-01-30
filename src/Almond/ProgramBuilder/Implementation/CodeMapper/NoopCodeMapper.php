<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\CodeMapper;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceNode;
use Walnut\Lang\Almond\Engine\Blueprint\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Function\FunctionBody;
use Walnut\Lang\Almond\Engine\Blueprint\Function\UserlandFunction;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\Method\UserlandMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\CodeMapper;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\SourceLocator;

final readonly class NoopCodeMapper implements CodeMapper, SourceLocator {
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