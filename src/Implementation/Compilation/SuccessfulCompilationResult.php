<?php

namespace Walnut\Lang\Implementation\Compilation;

use Walnut\Lang\Blueprint\AST\Node\RootNode;
use Walnut\Lang\Blueprint\Compilation\CompilationContext;
use Walnut\Lang\Blueprint\Compilation\SuccessfulCompilationResult as SuccessfulCompilationResultInterface;
use Walnut\Lang\Blueprint\Program\Program;

final readonly class SuccessfulCompilationResult implements SuccessfulCompilationResultInterface {
	public function __construct(
		public RootNode $ast,
		public Program $program,
		public CompilationContext $compilationContext
	) {}
}