<?php

namespace Walnut\Lang\Implementation\Compilation\Module;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Implementation\Compilation\Module\PackageConfiguration\PackageConfiguration;
use Walnut\Lang\Implementation\Compilation\Module\SourceFinder\PackageBasedSourceFinder;

final class PackageBasedSourceFinderTest extends TestCase {

	private PackageBasedSourceFinder $packageBasedSourceFinder;

	protected function setUp(): void {
		parent::setUp();

		$this->packageBasedSourceFinder = new PackageBasedSourceFinder(
			new PackageConfiguration(
				__DIR__,
				[]
			)
		);
	}

	public function testSourceNotFound(): void {
		$this->assertFalse($this->packageBasedSourceFinder->sourceExists('path.test.nut'));
		$this->assertNull($this->packageBasedSourceFinder->readSource('path.test.nut'));
	}
}