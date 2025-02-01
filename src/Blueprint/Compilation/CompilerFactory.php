<?php

namespace Walnut\Lang\Blueprint\Compilation;

interface CompilerFactory {
	public function compiler(
		string|null $templateSourceRoot,
		string ... $sourceRoots
	): Compiler;
}