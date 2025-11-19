<?php

namespace Walnut\Lang\Implementation\Compilation\Module\SourceFinder;

use ArrayAccess;
use Walnut\Lang\Blueprint\Compilation\Module\SourceFinder;

final readonly class InMemorySourceFinder implements SourceFinder {
	/** @param array<string, string>|ArrayAccess<string, string> $memoryMap */
	public function __construct(private array|ArrayAccess $memoryMap) {}

	public function sourceExists(string $sourceName): bool {
		/** @phpstan-ignore-next-line argument.type */
		return array_key_exists($sourceName, $this->memoryMap);
	}
	public function readSource(string $sourceName): string|null {
		return $this->memoryMap[$sourceName] ?? null;
	}
}