<?php

namespace Walnut\Lang\Test\Almond\Unsorted;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class DuplicationInSubsetTest extends CodeExecutionTestHelper {

	public function testDuplicateEnumerationValue(): void {
		$this->executeErrorCodeSnippet(
			"Enumeration value 'A' is listed 2 times in enumeration 'Dup'",
			"null;",
			<<<NUT
				Dup := (A, B, A, C, D);
			NUT
		);
	}

	public function testDuplicateEnumerationSubsetValue(): void {
		$this->executeErrorCodeSnippet(
			"Enumeration value 'C' is listed 2 times in subset type",
			"null;",
			<<<NUT
				Dup := (A, B, C, D);
				DupSub = Dup[A, C, C];
			NUT
		);
	}

	public function testDuplicateIntegerSubsetValue(): void {
		$this->executeErrorCodeSnippet(
			"Integer subset value '2' is listed 2 times in subset type",
			"null;",
			<<<NUT
				Dup = Integer[1, 2, 3, 2];
			NUT
		);
	}

	public function testDuplicateRealSubsetValue(): void {
		$this->executeErrorCodeSnippet(
			"Real subset value '2.1' is listed 2 times in subset type",
			"null;",
			<<<NUT
				Dup = Real[1.3, 2.1, -3, 2.1];
			NUT
		);
	}

	public function testDuplicateRealSubsetValueWithZeroes(): void {
		$this->executeErrorCodeSnippet(
		'The type "Real[1.3, 2, -3.14, 2.000]" already contains the value "2.000"',
			"null;",
			<<<NUT
				Dup = Real[1.3, 2, -3.14, 2.000];
			NUT
		);
	}

	public function testDuplicateRealSubsetValueWithZeroesAfterNonZero(): void {
		$this->executeErrorCodeSnippet(
			'The type "Real[1.3, 2.17, -3.14, 2.17000]" already contains the value "2.17000"',
			"null;",
			<<<NUT
				Dup = Real[1.3, 2.17, -3.14, 2.17000];
			NUT
		);
	}

	public function testDuplicateRealSubsetValueForZero(): void {
		$this->executeErrorCodeSnippet(
			'The type "Real[1.3, 0, -3.14, 0.000]" already contains the value "0.000"',
			"null;",
			<<<NUT
				Dup = Real[1.3, 0, -3.14, 0.000];
			NUT
		);
	}

	public function testDuplicateStringSubsetValue(): void {
		$this->executeErrorCodeSnippet(
			"String subset value 'hi' is listed 3 times in subset type",
			"null;",
			<<<NUT
				Dup = String['hi', 'ho', 'hi', 'hi', 'bye'];
			NUT
		);
	}

}