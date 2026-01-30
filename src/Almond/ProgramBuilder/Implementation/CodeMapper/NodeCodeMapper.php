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
use WeakMap;

final class NodeCodeMapper implements CodeMapper, SourceLocator {

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