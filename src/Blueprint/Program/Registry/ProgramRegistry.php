<?php

namespace Walnut\Lang\Blueprint\Program\Registry;

use Walnut\Lang\Blueprint\Code\Scope\VariableValueScope;

interface ProgramRegistry {
	public function typeRegistry(): TypeRegistry;
	public function valueRegistry(): ValueRegistry;
	public function expressionRegistry(): ExpressionRegistry;
	public function globalScope(): VariableValueScope;
}