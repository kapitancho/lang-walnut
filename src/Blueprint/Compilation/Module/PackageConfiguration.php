<?php

namespace Walnut\Lang\Blueprint\Compilation\Module;

interface PackageConfiguration {
	public string $defaultRoot { get; }
	/** @var array<string, string> $packageRoots */
	public array $packageRoots { get; }
}