<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Any;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsJsonValueTest extends CodeExecutionTestHelper {

	public function testAsJsonValueNonJson(): void {
		$result = $this->executeCodeSnippet("MyAtom->asJsonValue;", "MyAtom := ();");
		$this->assertEquals("@InvalidJsonValue![value: MyAtom]", $result);
	}

	public function testAsJsonValueOpen(): void {
		$result = $this->executeCodeSnippet("MyOpen(3)->asJsonValue;", "MyOpen := #Integer;");
		$this->assertEquals("3", $result);
	}

	public function testAsJsonValueOpenSafe(): void {
		$result = $this->executeCodeSnippet("getJson(MyOpen(3));", "
			MyOpen := #Integer;
		", "
			getJson = ^value: MyOpen => JsonValue :: value->asJsonValue;
		");
		$this->assertEquals("3", $result);
	}

	public function testAsJsonValueOpenSafeJson(): void { return;
		$result = $this->executeCodeSnippet("getJson(MyOpen[a: 1, b: null]);", "
			MyOpen := #[a: Integer, b: JsonValue];
		", "
			getJson = ^value: MyOpen => JsonValue :: value->asJsonValue;
		");
		$this->assertEquals("[a: 1, b: null]", $result);
	}

	public function testAsJsonValueOpenBroken(): void {
		$this->executeErrorCodeSnippet(
			"Function body return type 'Result<JsonValue, InvalidJsonValue>' is not compatible with declared return type 'JsonValue'.",
			"getJson(MyOpen[a: 1, b: ^ :: 1]);", "
			MyOpen := #[a: Integer, b: ^Null => Any];
		", "
			getJson = ^value: MyOpen => JsonValue :: value->asJsonValue;
		");
	}

	public function testAsJsonValueOpenSafeWithCast(): void {
		$result = $this->executeCodeSnippet("getJson(MyOpen[a: 1, b: MyNested[x: ^ :: 1]]);", "
			MyNested := #[x: ^Null => Any];
			MyOpen := #[a: Integer, b: MyNested];
			MyNested ==> JsonValue :: 1;
		", "
			getJson = ^value: MyOpen => JsonValue :: value->asJsonValue;
		");
		$this->assertEquals("[a: 1, b: 1]", $result);
	}

	public function testAsJsonValueOpenSafeWithCastError(): void {
		$this->executeErrorCodeSnippet(
			"Function body return type 'Result<JsonValue, InvalidJsonValue>' is not compatible with declared return type 'JsonValue'.",
			"getJson(MyOpen[a: 1, b: MyNested[x: ^ :: 1]]);", "
			MyNested := #[x: ^Null => Any];
			MyOpen := #[a: Integer, b: MyNested];
			MyNested ==> JsonValue @ InvalidJsonValue :: 1;",
			"
			getJson = ^value: MyOpen => JsonValue :: value->asJsonValue;
		");
	}

	public function testAsJsonValueJson(): void {
		$result = $this->executeCodeSnippet("null->asJsonValue;");
		$this->assertEquals("null", $result);
	}

}