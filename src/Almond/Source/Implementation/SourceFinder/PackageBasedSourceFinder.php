<?php

namespace Walnut\Lang\Almond\Source\Implementation\SourceFinder;

use Walnut\Lang\Almond\Source\Blueprint\PackageConfiguration\PackageConfiguration;
use Walnut\Lang\Almond\Source\Blueprint\PackageConfiguration\PackageConfigurationProvider;
use Walnut\Lang\Almond\Source\Blueprint\SourceFinder\SourceFinder;

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

		$content = file_exists($path) && is_readable($path) ? file_get_contents($path) : null;
		return is_string($content) ? $content : null;
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