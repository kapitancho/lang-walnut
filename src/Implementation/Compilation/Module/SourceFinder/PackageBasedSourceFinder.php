<?php

namespace Walnut\Lang\Implementation\Compilation\Module\SourceFinder;

use Walnut\Lang\Blueprint\Compilation\Module\PackageConfiguration;
use Walnut\Lang\Blueprint\Compilation\Module\PackageConfigurationProvider;
use Walnut\Lang\Blueprint\Compilation\Module\SourceFinder;

final readonly class PackageBasedSourceFinder implements SourceFinder {

	private PackageConfiguration $packageConfiguration;

	public function __construct(PackageConfigurationProvider $packageConfigurationProvider) {
		$this->packageConfiguration = $packageConfigurationProvider->packageConfiguration;
	}

	public function sourceExists(string $sourceName): bool {
		$path = $this->pathFor($sourceName);
		return file_exists($path) && is_readable($path);
	}

	public function readSource(string $sourceName): string|null {
		$path = $this->pathFor($sourceName);
		return file_exists($path) && is_readable($path) ? file_get_contents($path) : null;
	}

	private function pathFor(string $sourceName): string {
		foreach($this->packageConfiguration->packageRoots as $packageRoot => $path) {
			if (str_starts_with($sourceName, $packageRoot . '/')) {
				return $path . substr($sourceName, strlen($packageRoot));
			}
		}
		return $this->packageConfiguration->defaultRoot . '/' . $sourceName;
	}

}