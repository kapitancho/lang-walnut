<?php

namespace Walnut\Lang\Implementation\Compilation\Module;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Implementation\Compilation\Module\SourceFinder\CallbackSourceFinder;

final class CallbackSourceFinderTest extends TestCase {

	private CallbackSourceFinder $callbackSourceFinder;

	protected function setUp(): void {
		parent::setUp();

		$this->callbackSourceFinder = new CallbackSourceFinder([
			'path1.test.nut' => fn(): string => 'source code 11',
			'path2.test.nut' => fn(): null => null,
		]);
	}

	public function testSourceFoundFirst(): void {
		$this->assertTrue($this->callbackSourceFinder->sourceExists('path1.test.nut'));
		$this->assertEquals('source code 11', $this->callbackSourceFinder->readSource('path1.test.nut'));
	}

	public function testSourceNotFoundCallback(): void {
		$this->assertFalse($this->callbackSourceFinder->sourceExists('path2.test.nut'));
		$this->assertNull($this->callbackSourceFinder->readSource('path2.test.nut'));
	}

	public function testSourceNotFoundNoCallback(): void {
		$this->assertFalse($this->callbackSourceFinder->sourceExists('path3.test.nut'));
		$this->assertNull($this->callbackSourceFinder->readSource('path3.test.nut'));
	}
}