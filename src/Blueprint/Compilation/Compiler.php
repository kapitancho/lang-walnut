<?php

namespace Walnut\Lang\Blueprint\Compilation;

interface Compiler {
	public function compile(string $source): CompilationResult;
}