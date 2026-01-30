<?php

namespace Walnut\Lang\Almond\Source\Blueprint\SourceFinder;

interface SourceFinder {
	public function sourceExists(string $sourceName): bool;
	public function readSource(string $sourceName): string|null;
}