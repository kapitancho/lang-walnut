<?php

namespace Walnut\Lang\Blueprint\Compilation;

use Walnut\Lang\Blueprint\Program\Program;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;

final readonly class CompilationResult {
	public function __construct(
		public Program $program,
		public ProgramRegistry $programRegistry
	) {}
}