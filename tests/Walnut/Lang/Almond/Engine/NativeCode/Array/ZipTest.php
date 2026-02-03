<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class ZipTest extends CodeExecutionTestHelper {

	public function testZipEmptyEmpty(): void {
		$result = $this->executeCodeSnippet("[]->zip([]);");
		$this->assertEquals("[]", $result);
	}

	public function testZipEmptyNonEmpty(): void {
		$result = $this->executeCodeSnippet("[]->zip[1];");
		$this->assertEquals("[]", $result);
	}

	public function testZipNonEmptyEmpty(): void {
		$result = $this->executeCodeSnippet("['a']->zip([]);");
		$this->assertEquals("[]", $result);
	}

	public function testZipNonEmptyEqualSize(): void {
		$result = $this->executeCodeSnippet("['a', 'bcd', 'ef']->zip[1, 3, 2];");
		$this->assertEquals("[['a', 1], ['bcd', 3], ['ef', 2]]", $result);
	}

	public function testZipNonEmptyShorterParam(): void {
		$result = $this->executeCodeSnippet("['a', 'bcd', 'ef', 'ghij']->zip[1, 3, 2];");
		$this->assertEquals("[['a', 1], ['bcd', 3], ['ef', 2]]", $result);
	}

	public function testZipNonEmptyLongerParam(): void {
		$result = $this->executeCodeSnippet("['a', 'bcd', 'ef']->zip[1, 3, 2, 4];");
		$this->assertEquals("[['a', 1], ['bcd', 3], ['ef', 2]]", $result);
	}

	public function testZipReturnTypeFinite(): void {
		$result = $this->executeCodeSnippet("zipMap[['a', 'bcd', 'ef'], [1, 3, 2]]",
			valueDeclarations: "
				zipMap = ^p: [Array<String<1..3>, 2..5>, Array<Integer<0..10>, 3..6>] => Array<[String<1..3>, Integer<0..10>], 2..5> :: 
					p.0->zip(p.1);
			"
		);
		$this->assertEquals("[['a', 1], ['bcd', 3], ['ef', 2]]", $result);
	}

	public function testZipReturnTypeInfiniteTarget(): void {
		$result = $this->executeCodeSnippet("zipMap[['a', 'bcd', 'ef'], [1, 3, 2]]",
			valueDeclarations: "
				zipMap = ^p: [Array<String<1..3>, 2..>, Array<Integer<0..10>, 3..6>] => Array<[String<1..3>, Integer<0..10>], 2..6> :: 
					p.0->zip(p.1);
			"
		);
		$this->assertEquals("[['a', 1], ['bcd', 3], ['ef', 2]]", $result);
	}

	public function testZipReturnTypeInfiniteParameter(): void {
		$result = $this->executeCodeSnippet("zipMap[['a', 'bcd', 'ef'], [1, 3, 2]]",
			valueDeclarations: "
				zipMap = ^p: [Array<String<1..3>, ..5>, Array<Integer<0..10>, 3..>] => Array<[String<1..3>, Integer<0..10>], ..5> :: 
					p.0->zip(p.1);
			"
		);
		$this->assertEquals("[['a', 1], ['bcd', 3], ['ef', 2]]", $result);
	}


	public function testZipTuples(): void {
		$result = $this->executeCodeSnippet("true",
			valueDeclarations: "
				zipNoRestEqual = ^p: [[String, Integer, Boolean], [Real, Boolean, Integer]] => [[String, Real], [Integer, Boolean], [Boolean, Integer]] :: p.0->zip(p.1);
				zipNoRestParameterShorter = ^p: [[String, Integer, Boolean], [Real, Boolean]] => [[String, Real], [Integer, Boolean]] :: p.0->zip(p.1);
				zipNoRestTargetShorter = ^p: [[String, Integer], [Real, Boolean, Integer]] => [[String, Real], [Integer, Boolean]] :: p.0->zip(p.1);
				
				zipWithTargetRest = ^p: [[String, Integer, Boolean, ...Real], [Real, Boolean, Integer]] => [[String, Real], [Integer, Boolean], [Boolean, Integer]] :: p.0->zip(p.1);
				zipWithTargetRestParameterShorter = ^p: [[String, Integer, Boolean, ...Real], [Real, Boolean]] => [[String, Real], [Integer, Boolean]] :: p.0->zip(p.1);
				zipWithTargetRestTargetShorter = ^p: [[String, Integer, ...Real], [Real, Boolean, Integer]] => [[String, Real], [Integer, Boolean], [Real, Integer]] :: p.0->zip(p.1);
				
				zipWithParameterRest = ^p: [[String, Integer, Boolean], [Real, Boolean, Integer, ...String]] => [[String, Real], [Integer, Boolean], [Boolean, Integer]] :: p.0->zip(p.1);
				zipWithParameterRestParameterShorter = ^p: [[String, Integer, Boolean], [Real, Boolean, ...String]] => [[String, Real], [Integer, Boolean], [Boolean, String]] :: p.0->zip(p.1);
				zipWithParameterRestTargetShorter = ^p: [[String, Integer], [Real, Boolean, Integer, ...String]] => [[String, Real], [Integer, Boolean]] :: p.0->zip(p.1);
				
				zipWithBothRest = ^p: [[String, Integer, Boolean, ...Real], [Real, Boolean, Integer, ...String]] => [[String, Real], [Integer, Boolean], [Boolean, Integer], ...[Real, String]] :: p.0->zip(p.1);
				zipWithBothRestParameterShorter = ^p: [[String, Integer, Boolean, ...Real], [Real, Boolean, ...String]] => [[String, Real], [Integer, Boolean], [Boolean, String], ...[Real, String]] :: p.0->zip(p.1);
				zipWithBothRestTargetShorter = ^p: [[String, Integer, ...Real], [Real, Boolean, Integer, ...String]] => [[String, Real], [Integer, Boolean], [Real, Integer], ...[Real, String]] :: p.0->zip(p.1);
			"
		);
		$this->assertEquals("true", $result);
	}

	public function testZipInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "['a', 'bcd', 'ef']->zip(5);");
	}

}