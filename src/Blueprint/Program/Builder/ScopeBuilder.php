<?php

namespace Walnut\Lang\Blueprint\Program\Builder;

use Walnut\Lang\Blueprint\Code\Scope\VariableValueScope;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Value\Value;

interface ScopeBuilder {
    public function addVariable(VariableNameIdentifier $name, Value $value): ScopeBuilder;

	public function build(): VariableValueScope;
}