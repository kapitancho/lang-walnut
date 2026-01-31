<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Blueprint;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceNode;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\FunctionBody;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland\UserlandMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\VariableName;

interface CodeMapper {
	public function mapNode(
		SourceNode $node,
		Expression|Type|Value|FunctionBody|UserlandMethod|TypeName|VariableName|MethodName|EnumerationValueName $element
	): void;
}