<?php

namespace Walnut\Lang\Implementation\Compilation\Module\PackageConfiguration;

use Walnut\Lang\Blueprint\Compilation\Module\PackageConfiguration as PackageConfigurationInterface;
use Walnut\Lang\Blueprint\Compilation\Module\PackageConfigurationProvider;

final class PackageConfiguration implements PackageConfigurationInterface, PackageConfigurationProvider {
	/** @param array<string, string> $packageRoots */
	public function __construct(public string $defaultRoot, public array $packageRoots) {}

	public PackageConfiguration $packageConfiguration {
		get {
			return $this;
		}
	}
}