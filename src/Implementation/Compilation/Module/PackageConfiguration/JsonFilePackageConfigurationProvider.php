<?php

namespace Walnut\Lang\Implementation\Compilation\Module\PackageConfiguration;

use RuntimeException;
use Walnut\Lang\Blueprint\Compilation\Module\PackageConfiguration as PackageConfigurationInterface;
use Walnut\Lang\Blueprint\Compilation\Module\PackageConfigurationProvider;

final class JsonFilePackageConfigurationProvider implements PackageConfigurationProvider {

	private readonly PackageConfigurationInterface $configurationData;

	public function __construct(private readonly string $filePath) {
	}

	/** @throws RuntimeException */
	public PackageConfigurationInterface $packageConfiguration {
		get {
			return $this->configurationData ??= $this->readConfiguration();
		}
	}

	/** @throws RuntimeException */
	private function readConfiguration(): PackageConfigurationInterface {
		$content = @file_get_contents($this->filePath);
		if ($content === false) {
			throw new RuntimeException(
				sprintf(
					"Unable to read package configuration file at '%s'.", $this->filePath
				)
			);
		}
		$data = json_decode($content, true);
		if (
			is_array($data) &&
			isset($data['sourceRoot']) &&
			is_string($data['sourceRoot']) &&
			isset($data['packages']) &&
			is_array($data['packages']) &&
			array_all(array_keys($data['packages']), fn(mixed $package): bool => is_string($package)) &&
			array_all($data['packages'], fn(mixed $package): bool => is_string($package))
		) {
			return new PackageConfiguration(
				$data['sourceRoot'],
				$data['packages']
			);
		}
		throw new RuntimeException(
			sprintf(
				"Invalid package configuration format in file '%s'.",
				$this->filePath
			)
		);
	}
}