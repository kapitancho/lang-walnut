<?php

namespace Walnut\Lang\Blueprint\Code\Analyser;

use LogicException;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Function\CustomMethod;
use Walnut\Lang\Blueprint\Function\FunctionBody;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;

class AnalyserException extends LogicException {

	public function __construct(
		string $message,
		public Type|Value|Expression|FunctionBody|CustomMethod|null $target = null,
	) {
		parent::__construct($message);
	}

	public function withTarget(Type|Value|Expression|FunctionBody|CustomMethod $target): self {
		return new self(
			$this->message,
			$this->target,//$target
		);
	}

	public function __toString(): string {
		return $this->message;
	}

}