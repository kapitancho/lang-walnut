<?php

namespace Walnut\Lang\Blueprint\Compilation;

interface CompilationErrorBuilder {
	/** @return list<CompilationErrorEntry> */
	public function build(CompilationException $exception): array;
}