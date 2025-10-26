<?php

namespace Walnut\Lang\Blueprint\Compilation\Module;

interface PackageConfigurationProvider {
	public PackageConfiguration $packageConfiguration { get; }
}