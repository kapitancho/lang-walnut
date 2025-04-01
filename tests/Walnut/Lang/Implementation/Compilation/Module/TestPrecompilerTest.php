<?php

namespace Walnut\Lang\Implementation\Compilation\Module;

use PHPUnit\Framework\TestCase;

final class TestPrecompilerTest extends TestCase {

	public function testOk(): void {
		$result = new TestPrecompiler()->precompileSourceCode("modx", "source");
		$this->assertStringContainsString("~TestCases", $result);
	}

}