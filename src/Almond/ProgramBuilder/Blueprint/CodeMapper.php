<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Blueprint;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceNode;
use Walnut\Lang\Almond\Engine\Blueprint\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Function\FunctionBody;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\Method\UserlandMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

interface CodeMapper {
	public function mapNode(
		SourceNode $node,
		Expression|Type|Value|FunctionBody|UserlandMethod|TypeName|VariableName|MethodName|EnumerationValueName $element
	): void;
}