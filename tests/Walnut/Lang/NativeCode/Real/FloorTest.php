<?php

namespace Walnut\Lang\NativeCode\Real;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class FloorTest extends CodeExecutionTestHelper {

	public function testFloorPositive(): void {
		$result = $this->executeCodeSnippet("3.14->floor;");
		$this->assertEquals("3", $result);
	}

	public function testFloorPositiveRounding(): void {
		$result = $this->executeCodeSnippet("3.77->floor;");
		$this->assertEquals("3", $result);
	}

	public function testFloorNegative(): void {
		$result = $this->executeCodeSnippet("-3.14->floor;");
		$this->assertEquals("-4", $result);
	}

	public function testFloorNegativeRounding(): void {
		$result = $this->executeCodeSnippet("-3.77->floor;");
		$this->assertEquals("-4", $result);
	}
}