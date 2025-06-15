<?php

namespace Walnut\Lang\Implementation\Function;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class FunctionBodyTest extends CodeExecutionTestHelper {

	public function testFunctionBodyTuple(): void {
		$result = $this->executeCodeSnippet("tup(MyTuple['a', 2])", <<<NUT
			MyTuple := #[String, Integer]; 
			tup = ^MyTuple => Integer :: {#0->length} + {#1};
		NUT);
		$this->assertEquals('3', $result);
	}

	public function testFunctionBodyRecord(): void {
		$result = $this->executeCodeSnippet("rec(MyRecord[a: 'a', b: 2])", <<<NUT
			MyRecord := #[a: String, b: Integer]; 
			rec = ^MyRecord => Integer :: {#a->length} + {#b};
		NUT);
		$this->assertEquals('3', $result);
	}

	public function testFunctionBodyRecordOptionalKey(): void {
		$result = $this->executeCodeSnippet("rec(MyRecord[a: 'a', b: 2, c: 3.14])", <<<NUT
			MyRecord := #[a: String, b: Integer, c: ?Real]; 
			rec = ^MyRecord => Result<Real, MapItemNotFound> :: {#a->length} + {#b} + ?noError(#c);
		NUT);
		$this->assertEquals('6.14', $result);
	}

	public function testFunctionBodyRecordOptionalKeyMissing(): void {
		$result = $this->executeCodeSnippet("rec(MyRecord[a: 'a', b: 2])", <<<NUT
			MyRecord := #[a: String, b: Integer, c: ?Real]; 
			rec = ^MyRecord => Result<Real, MapItemNotFound> :: {#a->length} + {#b} + ?noError(#c);
		NUT);
		$this->assertEquals("@MapItemNotFound!!!!![key: 'c']", $result);
	}

	public function testFunctionBodyError(): void {
		$this->executeErrorCodeSnippet("Unknown variable 'x' in function body", '', <<<NUT
			noVar = ^Any => Any :: x;
		NUT);
	}

}