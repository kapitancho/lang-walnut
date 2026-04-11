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
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\PositionalLocator;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\SourceNodeLocator;
use WeakMap;

final class NodeCodeMapper implements CodeMapper, SourceNodeLocator, PositionalLocator {

	/** @var WeakMap<Expression|Value|Type|FunctionBody|UserlandMethod|TypeName|VariableName|MethodName|EnumerationValueName, SourceNode> */
	private WeakMap $nodeMap;

	/**
	 * Positional index: moduleName → list of [startOffset, endOffset, element].
	 * Elements are kept alive by this array for the lifetime of the mapper.
	 *
	 * @var array<string, list<array{0: int, 1: int, 2: Expression|Value|Type|FunctionBody|UserlandMethod|TypeName|VariableName|MethodName|EnumerationValueName}>>
	 */
	private array $positionalIndex = [];

	public function __construct() {
		$this->nodeMap = new WeakMap();
	}

	public function mapNode(
		SourceNode $node,
		Expression|Type|Value|FunctionBody|UserlandMethod|TypeName|VariableName|MethodName|EnumerationValueName $element
	): void {
		$this->nodeMap[$element] = $node;

		$location = $node->sourceLocation;
		$this->positionalIndex[$location->moduleName][] = [
			$location->startPosition->offset,
			$location->endPosition->offset,
			$element,
		];
	}

	public function reset(): void {
		$this->nodeMap = new WeakMap();
		$this->positionalIndex = [];
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

	public function findFirst(string $moduleName, callable $predicate): mixed {
		$entries = $this->positionalIndex[$moduleName] ?? [];
		usort($entries, static fn(array $a, array $b): int => $a[0] <=> $b[0]);
		foreach ($entries as [$start, $end, $element]) {
			if ($predicate($element, $start, $end)) {
				return $element;
			}
		}
		return null;
	}

	public function findLast(string $moduleName, callable $predicate): mixed {
		$entries = $this->positionalIndex[$moduleName] ?? [];
		usort($entries, static fn(array $a, array $b): int => $b[0] <=> $a[0]); // descending
		foreach ($entries as [$start, $end, $element]) {
			if ($predicate($element, $start, $end)) {
				return $element;
			}
		}
		return null;
	}

	public function elementsAtOffset(string $moduleName, int $offset): array {
		$entries = $this->positionalIndex[$moduleName] ?? [];

		$matching = [];
		foreach ($entries as [$start, $end, $element]) {
			if ($start <= $offset && $offset <= $end) {
				$matching[] = [$end - $start, $element];
			}
		}

		usort($matching, static fn(array $a, array $b): int => $a[0] <=> $b[0]);

		return array_column($matching, 1);
	}
}
