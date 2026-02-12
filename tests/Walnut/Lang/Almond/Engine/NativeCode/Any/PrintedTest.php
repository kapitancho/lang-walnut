<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Any;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class PrintedTest extends CodeExecutionTestHelper {

	public function testPrintedNonJson(): void {
		$result = $this->executeCodeSnippet("MyAtom->printed;", "MyAtom := ();");
		$this->assertEquals("'MyAtom'", $result);
	}

	public function testPrintedNull(): void {
		$result = $this->executeCodeSnippet("null->printed;");
		$this->assertEquals("'null'", $result);
	}

	public function testPrintedTrue(): void {
		$result = $this->executeCodeSnippet("true->printed;");
		$this->assertEquals("'true'", $result);
	}

	public function testPrintedFalse(): void {
		$result = $this->executeCodeSnippet("false->printed;");
		$this->assertEquals("'false'", $result);
	}

	public function testPrintedInteger(): void {
		$result = $this->executeCodeSnippet("5->printed;");
		$this->assertEquals("'5'", $result);
	}

	public function testPrintedReal(): void {
		$result = $this->executeCodeSnippet("3.14->printed;");
		$this->assertEquals("'3.14'", $result);
	}

	public function testPrintedString(): void {
		$result = $this->executeCodeSnippet("'hi'->printed;");
		$this->assertEquals("'\`hi\`'", $result);
	}

	public function testPrintedTuple(): void {
		$result = $this->executeCodeSnippet("[1, 2]->printed;");
		$this->assertEquals("'[1, 2]'", $result);
	}

	public function testPrintedRecord(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2]->printed;");
		$this->assertEquals("'[a: 1, b: 2]'", $result);
	}

	public function testPrintedSet(): void {
		$result = $this->executeCodeSnippet("[1; 2]->printed;");
		$this->assertEquals("'[1; 2]'", $result);
	}

	public function testPrintedMutable(): void {
		$result = $this->executeCodeSnippet("mutable{Real, 3.14}->printed;");
		$this->assertEquals("'mutable{Real, 3.14}'", $result);
	}

}