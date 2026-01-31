<?php

namespace Walnut\Lang\Almond\AST\Implementation\Builder;

use Walnut\Lang\Almond\AST\Blueprint\Builder\NameNodeBuilder as NameNodeBuilderInterface;
use Walnut\Lang\Almond\AST\Blueprint\Builder\SourceLocator;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Implementation\Node\Name\EnumerationValueNameNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Name\MethodNameNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Name\TypeNameNode;
use Walnut\Lang\Almond\AST\Implementation\Node\Name\VariableNameNode;

final readonly class NameNodeBuilder implements NameNodeBuilderInterface {

	public function __construct(private SourceLocator $sourceLocator) {}

	private function getSourceLocation(): SourceLocation {
		return $this->sourceLocator->getSourceLocation();
	}

	public function typeName(string $name): TypeNameNode {
		return new TypeNameNode($this->getSourceLocation(), $name);
	}
	public function variableName(string $name): VariableNameNode {
		return new VariableNameNode($this->getSourceLocation(), $name);
	}
	public function methodName(string $name): MethodNameNode {
		return new MethodNameNode($this->getSourceLocation(), $name);
	}
	public function enumerationValueName(string $name): EnumerationValueNameNode {
		return new EnumerationValueNameNode($this->getSourceLocation(), $name);
	}
}