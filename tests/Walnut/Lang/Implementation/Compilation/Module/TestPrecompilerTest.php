<?php

namespace Walnut\Lang\Test\Implementation\Compilation\Module;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Implementation\Compilation\Module\Precompiler\TestPrecompiler;

final class TestPrecompilerTest extends TestCase {

	public function testSourcePathWithTest(): void {
		$result = new TestPrecompiler()->determineSourcePath("path-test");
		$this->assertEquals('path.test.nut', $result);
	}

	public function testSourcePathWithoutTest(): void {
		$result = new TestPrecompiler()->determineSourcePath("path");
		$this->assertNull($result);
	}

	public function testOk(): void {
		$result = new TestPrecompiler()->precompileSourceCode("modx", "source");
		$this->assertStringContainsString("~TestCases", $result);
	}

}