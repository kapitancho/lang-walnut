<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class ZipTest extends CodeExecutionTestHelper {

	public function testZipEmptyEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->zip([:]);");
		$this->assertEquals("[:]", $result);
	}

	public function testZipEmptyNonEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->zip[a: 1];");
		$this->assertEquals("[:]", $result);
	}

	public function testZipNonEmptyEmpty(): void {
		$result = $this->executeCodeSnippet("[a: 'a']->zip([:]);");
		$this->assertEquals("[:]", $result);
	}

	public function testZipNonEmptyEqualSize(): void {
		$result = $this->executeCodeSnippet("[a: 'a', b: 'bcd', c: 'ef']->zip[a: 1, b: 3, d: 2];");
		$this->assertEquals("[a: ['a', 1], b: ['bcd', 3]]", $result);
	}

	public function testZipNonEmptyShorterParam(): void {
		$result = $this->executeCodeSnippet("[a: 'a', b: 'bcd', c: 'ef', e: 'ghij']->zip[a: 1, b: 3, d: 2];");
		$this->assertEquals("[a: ['a', 1], b: ['bcd', 3]]", $result);
	}

	public function testZipNonEmptyLongerParam(): void {
		$result = $this->executeCodeSnippet("[a: 'a', b: 'bcd', c: 'ef']->zip[a: 1, b: 3, d: 2, e: 4];");
		$this->assertEquals("[a: ['a', 1], b: ['bcd', 3]]", $result);
	}

	public function testZipReturnTypeFinite(): void {
		$result = $this->executeCodeSnippet("zipMap[[a: 'a', b: 'bcd', c: 'ef'], [a: 1, b: 3, d: 2]]",
			valueDeclarations: "
				zipMap = ^p: [Map<String<1..3>, 2..5>, Map<Integer<0..10>, 3..6>] => Map<[String<1..3>, Integer<0..10>], 2..5> :: 
					p.0->zip(p.1);
			"
		);
		$this->assertEquals("[a: ['a', 1], b: ['bcd', 3]]", $result);
	}

	public function testZipReturnTypeInfiniteTarget(): void {
		$result = $this->executeCodeSnippet("zipMap[[a: 'a', b: 'bcd', c: 'ef'], [a: 1, b: 3, d: 2]]",
			valueDeclarations: "
				zipMap = ^p: [Map<String<1..3>, 2..>, Map<Integer<0..10>, 3..6>] => Map<[String<1..3>, Integer<0..10>], 2..6> :: 
					p.0->zip(p.1);
			"
		);
		$this->assertEquals("[a: ['a', 1], b: ['bcd', 3]]", $result);
	}

	public function testZipReturnTypeInfiniteParameter(): void {
		$result = $this->executeCodeSnippet("zipMap[[a: 'a', b: 'bcd', c: 'ef'], [a: 1, b: 3, d: 2]]",
			valueDeclarations: "
				zipMap = ^p: [Map<String<1..3>, ..5>, Map<Integer<0..10>, 3..>] => Map<[String<1..3>, Integer<0..10>], ..5> :: 
					p.0->zip(p.1);
			"
		);
		$this->assertEquals("[a: ['a', 1], b: ['bcd', 3]]", $result);
	}

	public function testZipRecords(): void {
		$result = $this->executeCodeSnippet("true",
			valueDeclarations: "
				zipMapNoRest = ^p: [[a: String, b: Integer, c: Boolean], [a: Real, b: Boolean, d: Integer]] => [a: [String, Real], b: [Integer, Boolean]] :: p.0->zip(p.1);
				zipMapNoRestTargetOptional = ^p: [[a: String, b: ?Integer, c: Boolean], [a: Real, b: Boolean, d: Integer]] => [a: [String, Real], b: ?[Integer, Boolean]] :: p.0->zip(p.1);
				zipMapNoRestParameterOptional = ^p: [[a: String, b: Integer, c: Boolean], [a: Real, b: ?Boolean, d: Integer]] => [a: [String, Real], b: ?[Integer, Boolean]] :: p.0->zip(p.1);
				
				zipMapWithTargetRest = ^p: [[a: String, b: Integer, c: Boolean, ...Real], [a: Real, b: Boolean, d: Integer]] => [a: [String, Real], b: [Integer, Boolean], d: ?[Real, Integer]] :: p.0->zip(p.1);
				zipMapWithParameterRest = ^p: [[a: String, b: Integer, c: Boolean], [a: Real, b: Boolean, d: Integer, ...String]] => [a: [String, Real], b: [Integer, Boolean], c: ?[Boolean, String]] :: p.0->zip(p.1);
				
				zipMapWithBothRest = ^p: [[a: String, b: Integer, c: Boolean, ...Real], [a: Real, b: Boolean, d: Integer, ...String]] => [a: [String, Real], b: [Integer, Boolean], c: ?[Boolean, String], d: ?[Real, Integer], ...[Real, String]] :: p.0->zip(p.1);
				zipMapWithBothRestTargetOptional = ^p: [[a: String, b: ?Integer, c: Boolean, ...Real], [a: Real, b: Boolean, d: Integer, ...String]] => [a: [String, Real], b: ?[Integer, Boolean], c: ?[Boolean, String], d: ?[Real, Integer], ...[Real, String]] :: p.0->zip(p.1);
				zipMapWithBothRestParameterOptional = ^p: [[a: String, b: Integer, c: Boolean, ...Real], [a: Real, b: ?Boolean, d: Integer, ...String]] => [a: [String, Real], b: ?[Integer, Boolean], c: ?[Boolean, String], d: ?[Real, Integer], ...[Real, String]] :: p.0->zip(p.1);
			"
		);
		$this->assertEquals("true", $result);
	}

	public function testZipInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[a: 'a', b: 'bcd', c: 'ef']->zip(5);");
	}

}