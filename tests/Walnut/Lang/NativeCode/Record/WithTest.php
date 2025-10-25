<?php

namespace Walnut\Lang\Test\NativeCode\Record;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class WithTest extends CodeExecutionTestHelper {

	public function testWithRecord(): void {
		$result = $this->executeCodeSnippet("recWith[t: [a: 'hi', b: 42], p: [b: -9, c: 3.14]];",
			valueDeclarations: "recWith = ^[t: [a: String, b: Integer], p: [b: Integer, c: Real]] 
				=> [a: String, b: Integer, c: Real] :: #t->with(#p);");
		$this->assertEquals("[a: 'hi', b: -9, c: 3.14]", $result);
	}

	public function testWithRecordExtended(): void {
		$result = $this->executeCodeSnippet(
			"recWith[[a: 'a', b: 'b', c: 'c', x: 3.14], [b: 2.17, e: 'e', y: false]];",
			"
			TypeA = [a: String, b: String, c: String, d: OptionalKey<Integer>, e: OptionalKey<Integer>, f: OptionalKey<Integer>, ... Real];
			TypeB = [b: Real, c: OptionalKey<Real>, e: String, f: OptionalKey<String>, ... Boolean];
			TypeC = [a: String|Boolean, b: Real, c: String|Real, d: OptionalKey<Integer|Boolean>, e: String, f: OptionalKey<Integer|String>, ... Real|Boolean];
			", "recWith = ^[TypeA, TypeB] => TypeC :: #.0->with(#.1);");
		$this->assertEquals(
			str_replace(' ', '', "[a: 'a', b: 2.17, c: 'c', x: 3.14, e: 'e', y: false]"),
			str_replace(["\n", "\t", " "], '', $result)
		);
	}


	public function testWithRecordMap(): void {
		$result = $this->executeCodeSnippet("recWith[t: [a: 'hi', b: 42], p: [b: -9, c: 3.14]];",
			valueDeclarations: "recWith = ^[t: [a: String, b: Integer], p: Map<Real>] 
				=> Map<String|Real, 2..> :: #t->with(#p);");
		$this->assertEquals("[a: 'hi', b: -9, c: 3.14]", $result);
	}

	public function testChunkInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[a: 1]->with(false);");
	}

}