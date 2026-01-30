<?php

namespace Walnut\Lang\Almond\Source\Implementation\SourceFinder;

use Walnut\Lang\Almond\Source\Blueprint\SourceFinder\SourceFinder;

final readonly class CallbackSourceFinder implements SourceFinder {
	/** @param array<string, callable(string): string|null> $callableMap */
	public function __construct(private array $callableMap) {}

	public function sourceExists(string $sourceName): bool {
		return array_key_exists($sourceName, $this->callableMap) &&
			/** @phpstan-ignore-next-line identical.alwaysTrue */
			$this->callableMap[$sourceName]($sourceName) !== null;
	}
	public function readSource(string $sourceName): string|null {
		$fn = $this->callableMap[$sourceName] ?? null;
		return $fn ? $fn($sourceName) : null;
	}
}