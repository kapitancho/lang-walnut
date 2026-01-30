<?php

namespace Walnut\Lang\Almond\Source\Implementation\PackageConfiguration;

use Walnut\Lang\Almond\Source\Blueprint\PackageConfiguration\PackageConfiguration as PackageConfigurationInterface;
use Walnut\Lang\Almond\Source\Blueprint\PackageConfiguration\PackageConfigurationProvider;

final class PackageConfiguration implements PackageConfigurationInterface, PackageConfigurationProvider {
	/** @param array<string, string> $packageRoots */
	public function __construct(public string $defaultRoot, public array $packageRoots) {}

	public PackageConfiguration $packageConfiguration {
		get {
			return $this;
		}
	}
}