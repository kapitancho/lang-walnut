<?php

namespace Walnut\Lang\NativeCode\Mutable;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class AsIntegerTest extends CodeExecutionTestHelper {

	public function testAsIntegerPositive(): void {
		$result = $this->executeCodeSnippet("mutable{Real, 3.14}->asInteger;");
		$this->assertEquals("3", $result);
	}

	public function testAsIntegerPositiveRounding(): void {
		$result = $this->executeCodeSnippet("mutable{Real, 3.77}->asInteger;");
		$this->assertEquals("3", $result);
	}

	public function testAsIntegerNegative(): void {
		$result = $this->executeCodeSnippet("mutable{Real, -3.14}->asInteger;");
		$this->assertEquals("-3", $result);
	}

	public function testAsIntegerNegativeRounding(): void {
		$result = $this->executeCodeSnippet("mutable{Real, -3.77}->asInteger;");
		$this->assertEquals("-3", $result);
	}

	public function testAsIntegerInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "mutable{Set, [;]}->asInteger;");
	}

}