<?php

namespace Walnut\Lang\Implementation\Compilation\Module;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Implementation\Compilation\Module\SourceFinder\InMemorySourceFinder;

final class InMemorySourceFinderTest extends TestCase {

	private InMemorySourceFinder $inMemorySourceFinder;

	protected function setUp(): void {
		parent::setUp();

		$this->inMemorySourceFinder = new InMemorySourceFinder([
			'path1.test.nut' => 'source code 11',
		]);
	}

	public function testSourceFoundFirst(): void {
		$this->assertTrue($this->inMemorySourceFinder->sourceExists('path1.test.nut'));
		$this->assertEquals('source code 11', $this->inMemorySourceFinder->readSource('path1.test.nut'));
	}

	public function testSourceNotFound(): void {
		$this->assertFalse($this->inMemorySourceFinder->sourceExists('path2.test.nut'));
		$this->assertNull($this->inMemorySourceFinder->readSource('path2.test.nut'));
	}
}