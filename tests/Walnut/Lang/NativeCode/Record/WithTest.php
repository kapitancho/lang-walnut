<?php

namespace Walnut\Lang\NativeCode\Record;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class WithTest extends CodeExecutionTestHelper {

	public function testWithRecord(): void {
		$result = $this->executeCodeSnippet("recWith[t: [a: 'hi', b: 42], p: [b: -9, c: 3.14]];",
			"recWith = ^[t: [a: String, b: Integer], p: [b: Integer, c: Real]] 
				=> [a: String, b: Integer, c: Real] :: #t->with(#p);");
		$this->assertEquals("[a: 'hi', b: -9, c: 3.14]", $result);
	}

	public function testWithSubtype(): void {
		$result = $this->executeCodeSnippet("recWith[t: MySubtype[a: 'hi', b: 42], p: [b: -9]];",
			"MySubtype <: [a: String, b: Integer]; recWith = ^[t: MySubtype, p: [b: Integer]] 
				=> MySubtype :: #t->with(#p);");
		$this->assertEquals("MySubtype[a: 'hi', b: -9]", $result);
	}

	public function testWithSubtypeConstructedOk(): void {
		$result = $this->executeCodeSnippet("recWith[t: ?noError(MySubtype[a: 'hi', b: 42]), p: [b: 9]];",
			"MySubtype <: [a: String, b: Integer] @ String :: ?when(#b < 0) { => @'error'}; 
				recWith = ^[t: MySubtype, p: [b: Integer]] => Result<MySubtype, String> :: #t->with(#p);");
		$this->assertEquals("MySubtype[a: 'hi', b: 9]", $result);
	}

	public function testWithSubtypeConstructedError(): void {
		$result = $this->executeCodeSnippet("recWith[t: ?noError(MySubtype[a: 'hi', b: 42]), p: [b: -9]];",
			"MySubtype <: [a: String, b: Integer] @ String :: ?when(#b < 0) { => @'error'}; 
				recWith = ^[t: MySubtype, p: [b: Integer]] => Result<MySubtype, String> :: #t->with(#p);");
		$this->assertEquals("@'error'", $result);
	}

	public function testWithRecordMap(): void {
		$result = $this->executeCodeSnippet("recWith[t: [a: 'hi', b: 42], p: [b: -9, c: 3.14]];",
			"recWith = ^[t: [a: String, b: Integer], p: Map<Real>] 
				=> Map<String|Real, 2..> :: #t->with(#p);");
		$this->assertEquals("[a: 'hi', b: -9, c: 3.14]", $result);
	}

}