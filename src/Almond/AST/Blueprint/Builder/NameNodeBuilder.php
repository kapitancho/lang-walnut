<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Builder;

use Walnut\Lang\Almond\AST\Blueprint\Node\Name\EnumerationValueNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\MethodNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\TypeNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\VariableNameNode;

interface NameNodeBuilder {
	public function typeName(string $name): TypeNameNode;
	public function variableName(string $name): VariableNameNode;
	public function methodName(string $name): MethodNameNode;
	public function enumerationValueName(string $name): EnumerationValueNameNode;
}