<?php

namespace Walnut\Lang\Blueprint\Code\Scope;

use LogicException;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;

final class UnknownContextVariable extends LogicException {

	private function __construct(public VariableNameIdentifier $variableName) {
		parent::__construct((string)$this);
	}

	/** @throws UnknownContextVariable */
	public static function withName(VariableNameIdentifier $variableName): never {
		throw new self($variableName);
	}

	public function __toString(): string {
		return sprintf("Unknown context variable '%s'", $this->variableName);
	}
}