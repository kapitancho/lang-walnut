<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class CastAsTest extends CodeExecutionTestHelper {

	public function testCastAsNoCastAvailable(): void {
		$result = $this->executeCodeSnippet("MyAtom->as(type{Integer})",
			"MyAtom := ();");
		$this->assertEquals("@CastNotAvailable![from: type{MyAtom}, to: type{Integer}]", $result);
	}

	public function testCastAsCastAvailable(): void {
		$result = $this->executeCodeSnippet("MyAtom->as(type{Integer})",
			"MyAtom := (); MyAtom ==> Integer :: 42;");
		$this->assertEquals("42", $result);
	}

	public function testCastAsErrorCastAvailableOk(): void {
		$result = $this->executeCodeSnippet("MyAtom->as(type{Integer})",
			"MyAtom := (); MyAtom ==> Integer @ String :: 42;");
		$this->assertEquals("42", $result);
	}

	public function testCastAsErrorCastAvailableError(): void {
		$result = $this->executeCodeSnippet("MyAtom->as(type{Integer})",
			"MyAtom := (); MyAtom ==> Integer @ String :: @'error';");
		$this->assertEquals("@'error'", $result);
	}

}