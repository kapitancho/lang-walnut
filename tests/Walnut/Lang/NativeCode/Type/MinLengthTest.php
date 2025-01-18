<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class MinLengthTest extends CodeExecutionTestHelper {

	public function testMinLengthString(): void {
		$result = $this->executeCodeSnippet("type{String<2..>}->minLength;");
		$this->assertEquals("2", $result);
	}

	public function testMinLengthArray(): void {
		$result = $this->executeCodeSnippet("type{Array<String, 2..>}->minLength;");
		$this->assertEquals("2", $result);
	}

	public function testMinLengthMap(): void {
		$result = $this->executeCodeSnippet("type{Map<String, 2..>}->minLength;");
		$this->assertEquals("2", $result);
	}

	public function testMinLengthSet(): void {
		$result = $this->executeCodeSnippet("type{Set<String, 2..>}->minLength;");
		$this->assertEquals("2", $result);
	}

}