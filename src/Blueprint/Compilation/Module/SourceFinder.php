<?php

namespace Walnut\Lang\Blueprint\Compilation\Module;

interface SourceFinder {
	public function sourceExists(string $sourceName): bool;
	public function readSource(string $sourceName): string|null;
}