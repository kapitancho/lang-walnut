<?php

namespace Walnut\Lang\Almond\Source\Blueprint\PackageConfiguration;

interface PackageConfiguration {
	public string $defaultRoot { get; }
	/** @var array<string, string> $packageRoots */
	public array $packageRoots { get; }
}