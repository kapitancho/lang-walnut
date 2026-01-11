<?php

namespace Walnut\Lang\Implementation\Compilation;

use Walnut\Lang\Blueprint\AST\Node\RootNode;
use Walnut\Lang\Blueprint\Compilation\CompilationException;
use Walnut\Lang\Blueprint\Compilation\FailedCompilationResult as FailedCompilationResultInterface;
use Walnut\Lang\Blueprint\Program\ProgramContext;

final readonly class FailedCompilationResult implements FailedCompilationResultInterface {
	public function __construct(
		public ProgramContext $programContext,
		public RootNode|null $ast,
		public null $program,
		public CompilationException $errorState
	) {}
}