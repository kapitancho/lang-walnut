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
use WeakMap;

final class NodeCodeMapper implements CodeMapper, SourceNodeLocator {

	/** @var WeakMap<Expression|Value|Type|FunctionBody|UserlandMethod, SourceNode> */
	private WeakMap $nodeMap;

	public function __construct(
	) {
		$this->nodeMap = new WeakMap();
	}

	public function mapNode(
		SourceNode $node,
		Expression|Value|Type|FunctionBody|UserlandMethod|TypeName|VariableName|MethodName|EnumerationValueName $element
	): void {
		$this->nodeMap[$element] = $node;
	}

	public function reset(): void {
		$this->nodeMap = new WeakMap();
	}

	public function getSourceNode(Expression|Value|Type|FunctionBody|UserlandMethod|UserlandFunction $element): SourceNode|null {
		if ($element instanceof UserlandFunction) {
			$element = $element->functionBody;
		}
		return $this->nodeMap[$element] ?? null;
	}

	public function getSourceLocation(Expression|Value|Type|FunctionBody|UserlandMethod|UserlandFunction $element): SourceLocation|null {
		return $this->getSourceNode($element)?->sourceLocation;
	}
}