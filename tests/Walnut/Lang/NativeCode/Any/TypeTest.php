<?php

namespace Walnut\Lang\Test\NativeCode\Any;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class TypeTest extends CodeExecutionTestHelper {

	public function testTypeNull(): void {
		$result = $this->executeCodeSnippet("null->type;");
		$this->assertEquals("type{Null}", $result);
	}

	public function testTypeTrue(): void {
		$result = $this->executeCodeSnippet("true->type;");
		$this->assertEquals("type{True}", $result);
	}

	public function testTypeFalse(): void {
		$result = $this->executeCodeSnippet("false->type;");
		$this->assertEquals("type{False}", $result);
	}

	public function testTypeInteger(): void {
		$result = $this->executeCodeSnippet("5->type;");
		$this->assertEquals("type{Integer[5]}", $result);
	}

	public function testTypeReal(): void {
		$result = $this->executeCodeSnippet("5.5->type;");
		$this->assertEquals("type{Real[5.5]}", $result);
	}

	public function testTypeString(): void {
		$result = $this->executeCodeSnippet("'Hello'->type;");
		$this->assertEquals("type{String['Hello']}", $result);
	}

	public function testTypeTuple(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3]->type;");
		$this->assertEquals("type[Integer[1], Integer[2], Integer[3]]", $result);
	}

	public function testTypeRecord(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2]->type;");
		$this->assertEquals("type[a: Integer[1], b: Integer[2]]", $result);
	}

	public function testTypeSet(): void {
		$result = $this->executeCodeSnippet("[1; 2; 3]->type;");
		$this->assertEquals("type{Set<Integer[1, 2, 3], 3..3>}", $result);
	}

	public function testTypeError(): void {
		$result = $this->executeCodeSnippet("{@'error'}->type;");
		$this->assertEquals("type{Error<String['error']>}", $result);
	}

	public function testTypeAtom(): void {
		$result = $this->executeCodeSnippet("{MyAtom}->type;", "MyAtom := ();");
		$this->assertEquals("type{MyAtom}", $result);
	}

	public function testTypeEnumeration(): void {
		$result = $this->executeCodeSnippet("{MyEnumeration.C}->type;", "MyEnumeration := (A, B, C);");
		$this->assertEquals("type{MyEnumeration[C]}", $result);
	}

	public function testTypeOpen(): void {
		$result = $this->executeCodeSnippet("{MyOpen[a: 'value']}->type;", "MyOpen := #[a: String];");
		$this->assertEquals("type{MyOpen}", $result);
	}

	public function testTypeSealed(): void {
		$result = $this->executeCodeSnippet("{MySealed[a: 'value']}->type;", "MySealed := $[a: String];");
		$this->assertEquals("type{MySealed}", $result);
	}

	public function testTypeFunction(): void {
		$result = $this->executeCodeSnippet("{^Any => Integer :: 1}->type;");
		$this->assertEquals("type{^Any => Integer}", $result);
	}

	public function testTypeMutable(): void {
		$result = $this->executeCodeSnippet("{mutable{Integer, 1}}->type;");
		$this->assertEquals("type{Mutable<Integer>}", $result);
	}

	public function testTypeType(): void {
		$result = $this->executeCodeSnippet("type{Integer}->type;");
		$this->assertEquals("type{Type<Integer>}", $result);
	}

	public function testTypeParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type: Integer[42]",
			"'** hello **'->type(42);");
	}

}