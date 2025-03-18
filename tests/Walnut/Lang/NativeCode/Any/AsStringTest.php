<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Test\CodeExecutionTestHelper;

// ESSENTIAL TEST
final class AsStringTest extends CodeExecutionTestHelper {

	public function testAsStringNonJson(): void {
		$result = $this->executeCodeSnippet("MyAtom()->asString;", "MyAtom = :[];");
		$this->assertEquals("'MyAtom'", $result);
	}

	public function testAsStringNull(): void {
		$result = $this->executeCodeSnippet("null->asString;");
		$this->assertEquals("'null'", $result);
	}

	public function testAsStringTrue(): void {
		$result = $this->executeCodeSnippet("true->asString;");
		$this->assertEquals("'true'", $result);
	}

	public function testAsStringFalse(): void {
		$result = $this->executeCodeSnippet("false->asString;");
		$this->assertEquals("'false'", $result);
	}

	public function testAsStringInteger(): void {
		$result = $this->executeCodeSnippet("5->asString;");
		$this->assertEquals("'5'", $result);
	}

	public function testAsStringReal(): void {
		$result = $this->executeCodeSnippet("3.14->asString;");
		$this->assertEquals("'3.14'", $result);
	}

	public function testAsStringString(): void {
		$result = $this->executeCodeSnippet("'hi'->asString;");
		$this->assertEquals("'hi'", $result);
	}

	public function testAsStringTuple(): void {
		$result = $this->executeCodeSnippet("[1, 2]->asString;");
		$this->assertEquals("@CastNotAvailable[\n\tfrom: type[Integer[1], Integer[2]],\n\tto: type{String}\n]", $result);
	}

	public function testAsStringRecord(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2]->asString;");
		$this->assertEquals("@CastNotAvailable[\n\tfrom: type[a: Integer[1], b: Integer[2]],\n\tto: type{String}\n]", $result);
	}

	public function testAsStringSet(): void {
		$result = $this->executeCodeSnippet("[1; 2]->asString;");
		$this->assertEquals("@CastNotAvailable[\n\tfrom: type{Set<Integer[1, 2], 2..2>},\n\tto: type{String}\n]", $result);
	}

	public function testAsStringOpen(): void {
		$result = $this->executeCodeSnippet("{MyOpen[a: 'value']}->asString;", "MyOpen = #[a: String];");
		$this->assertEquals("@CastNotAvailable[from: type{MyOpen}, to: type{String}]", $result);
	}

	public function testAsStringSealed(): void {
		$result = $this->executeCodeSnippet("{MySealed[a: 'value']}->asString;", "MySealed = $[a: String];");
		$this->assertEquals("@CastNotAvailable[from: type{MySealed}, to: type{String}]", $result);
	}

	public function testAsStringAlias(): void {
		$result = $this->executeCodeSnippet("getReal()->asString;",
			"MyAlias = Real; getReal = ^ => MyAlias :: 3.14;");
		$this->assertEquals("'3.14'", $result);
	}

	public function testAsStringMutable(): void {
		$result = $this->executeCodeSnippet("mutable{Real, 3.14}->asString;");
		$this->assertEquals("'3.14'", $result);
	}

	public function testAsStringShape(): void {
		$result = $this->executeCodeSnippet("getReal()->shape(`Real)->asString;",
			"getReal = ^ => Shape<Real> :: 3.14;");
		$this->assertEquals("'3.14'", $result);
	}

	public function testAsStringType(): void {
		$result = $this->executeCodeSnippet("type{Integer}->asString;");
		$this->assertEquals("'Integer'", $result);
	}

}