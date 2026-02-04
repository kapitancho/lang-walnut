<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class MaxLengthTest extends CodeExecutionTestHelper {

	public function testMaxLengthString(): void {
		$result = $this->executeCodeSnippet("type{String<2..5>}->maxLength;");
		$this->assertEquals("5", $result);
	}

	public function testMaxLengthStringPlusInfinity(): void {
		$result = $this->executeCodeSnippet("type{String<2..>}->maxLength;");
		$this->assertEquals("PlusInfinity", $result);
	}

	public function testMaxLengthArray(): void {
		$result = $this->executeCodeSnippet("type{Array<String, 2..5>}->maxLength;");
		$this->assertEquals("5", $result);
	}

	public function testMaxLengthArrayPlusInfinity(): void {
		$result = $this->executeCodeSnippet("type{Array<String, 2..>}->maxLength;");
		$this->assertEquals("PlusInfinity", $result);
	}

	public function testMaxLengthMap(): void {
		$result = $this->executeCodeSnippet("type{Map<String, 2..5>}->maxLength;");
		$this->assertEquals("5", $result);
	}

	public function testMaxLengthMapPlusInfinity(): void {
		$result = $this->executeCodeSnippet("type{Map<String, 2..>}->maxLength;");
		$this->assertEquals("PlusInfinity", $result);
	}

	public function testMaxLengthSet(): void {
		$result = $this->executeCodeSnippet("type{Set<String, 2..5>}->maxLength;");
		$this->assertEquals("5", $result);
	}

	public function testMaxLengthSetPlusInfinity(): void {
		$result = $this->executeCodeSnippet("type{Set<String, 2..>}->maxLength;");
		$this->assertEquals("PlusInfinity", $result);
	}

}