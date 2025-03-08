<?php

namespace Walnut\Lang\Test\Implementation\Compilation\Module;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Implementation\Compilation\Module\PackageBasedModulePathFinder;

class PackageBasedModulePathFinderTest extends TestCase {

	private PackageBasedModulePathFinder $packageBasedModulePathFinder;

	protected function setUp(): void {
		parent::setUp();

		$this->packageBasedModulePathFinder = new PackageBasedModulePathFinder(
			'default', [
				'abc' => 'def',
				'ghi/jkl' => 'mno/pqr'
			]
		);
	}

	public function testDefaultPath(): void {
		$this->assertEquals(
			'default/vvv',
			$this->packageBasedModulePathFinder->pathFor('vvv')
		);
	}

	public function testSimplePackage(): void {
		// It matches the package 'abc' but misses the / so it's not a match.
		$this->assertEquals(
			'default/abc',
			$this->packageBasedModulePathFinder->pathFor('abc')
		);
		// It matches the package 'abc'.
		$this->assertEquals(
			'def/demo',
			$this->packageBasedModulePathFinder->pathFor('abc/demo')
		);
	}

	public function testNestedPackage(): void {
		$this->assertEquals(
			'mno/pqr/start',
			$this->packageBasedModulePathFinder->pathFor('ghi/jkl/start')
		);
	}

}