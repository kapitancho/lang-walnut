<?php

namespace Walnut\Lang\Implementation\Compilation\Module;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Implementation\Compilation\Module\SourceFinder\CompositeSourceFinder;
use Walnut\Lang\Implementation\Compilation\Module\SourceFinder\InMemorySourceFinder;

final class CompositeSourceFinderTest extends TestCase {

	private CompositeSourceFinder $compositeSourceFinder;

	protected function setUp(): void {
		parent::setUp();

		$this->compositeSourceFinder = new CompositeSourceFinder(
			new InMemorySourceFinder([
				'path1.test.nut' => 'source code 11',
			]),
			new InMemorySourceFinder([
				'path1.test.nut' => 'source code 21',
				'path2.test.nut' => 'source code 22',
			]),
		);
	}

	public function testSourceFoundFirst(): void {
		$this->assertTrue($this->compositeSourceFinder->sourceExists('path1.test.nut'));
		$this->assertEquals('source code 11', $this->compositeSourceFinder->readSource('path1.test.nut'));
	}

	public function testSourceFoundSecond(): void {
		$this->assertTrue($this->compositeSourceFinder->sourceExists('path2.test.nut'));
		$this->assertEquals('source code 22', $this->compositeSourceFinder->readSource('path2.test.nut'));
	}

	public function testSourceNotFound(): void {
		$this->assertFalse($this->compositeSourceFinder->sourceExists('path3.test.nut'));
		$this->assertNull($this->compositeSourceFinder->readSource('path3.test.nut'));
	}
}