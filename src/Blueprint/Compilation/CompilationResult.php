<?php

namespace Walnut\Lang\Blueprint\Compilation;

use Walnut\Lang\Blueprint\AST\Node\RootNode;
use Walnut\Lang\Blueprint\Program\Program;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;

final readonly class CompilationResult {
	public function __construct(
		public RootNode $ast,
		public Program $program,
		public ProgramRegistry $programRegistry
	) {}
}