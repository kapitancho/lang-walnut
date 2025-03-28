<?php

namespace Walnut\Lang\Feature\Type;

use Walnut\Lang\Blueprint\Compilation\AST\AstProgramCompilationException;
use Walnut\Lang\Test\CodeExecutionTestHelper;

final class DuplicationInSubsetTest extends CodeExecutionTestHelper {

	public function testDuplicateEnumerationValue(): void {
		try {
			$this->executeCodeSnippet(
				"null;",
				<<<NUT
					Dup = :[A, B, A, C, D];
				NUT
			);
			$this->fail("Expected AstProgramCompilationException not thrown");
		} catch (AstProgramCompilationException $e) {
			$this->assertStringContainsString(
				"Duplicate Enumeration value: 'A' in type 'Dup'",
				$e->getMessage()
			);
		}
	}

	public function testDuplicateEnumerationSubsetValue(): void {
		try {
			$this->executeCodeSnippet(
				"null;",
				<<<NUT
					Dup = :[A, B, C, D];
					DupSub = Dup[A, C, C];
				NUT
			);
			$this->fail("Expected AstProgramCompilationException not thrown");
		} catch (AstProgramCompilationException $e) {
			$this->assertStringContainsString(
				"Duplicate Enumeration value: 'C' in type 'Dup[A, C, C]'",
				$e->getMessage()
			);
		}
	}

	public function testDuplicateIntegerSubsetValue(): void {
		try {
			$this->executeCodeSnippet(
				"null;",
				<<<NUT
					Dup = Integer[1, 2, 3, 2];
				NUT
			);
			$this->fail("Expected AstProgramCompilationException not thrown");
		} catch (AstProgramCompilationException $e) {
			$this->assertStringContainsString(
				"Duplicate Integer value: '2' in type 'Integer[1, 2, 3, 2]'",
				$e->getMessage()
			);
		}
	}

	public function testDuplicateRealSubsetValue(): void {
		try {
			$this->executeCodeSnippet(
				"null;",
				<<<NUT
					Dup = Real[1.3, 2, -3.14, 2];
				NUT
			);
			$this->fail("Expected AstProgramCompilationException not thrown");
		} catch (AstProgramCompilationException $e) {
			$this->assertStringContainsString(
				"Duplicate Real value: '2' in type 'Real[1.3, 2, -3.14, 2]'",
				$e->getMessage()
			);
		}
	}

	public function testDuplicateRealSubsetValueWithZeroes(): void {
		try {
			$this->executeCodeSnippet(
				"null;",
				<<<NUT
					Dup = Real[1.3, 2, -3.14, 2.000];
				NUT
			);
			$this->fail("Expected AstProgramCompilationException not thrown");
		} catch (AstProgramCompilationException $e) {
			$this->assertStringContainsString(
				"Duplicate Real value: '2.000' in type 'Real[1.3, 2, -3.14, 2.000]'",
				$e->getMessage()
			);
		}
	}

	public function testDuplicateRealSubsetValueWithZeroesAfterNonZero(): void {
		try {
			$this->executeCodeSnippet(
				"null;",
				<<<NUT
					Dup = Real[1.3, 2.17, -3.14, 2.17000];
				NUT
			);
			$this->fail("Expected AstProgramCompilationException not thrown");
		} catch (AstProgramCompilationException $e) {
			$this->assertStringContainsString(
				"Duplicate Real value: '2.17000' in type 'Real[1.3, 2.17, -3.14, 2.17000]'",
				$e->getMessage()
			);
		}
	}

	public function testDuplicateRealSubsetValueForZero(): void {
		try {
			$this->executeCodeSnippet(
				"null;",
				<<<NUT
					Dup = Real[1.3, 0, -3.14, 0.000];
				NUT
			);
			$this->fail("Expected AstProgramCompilationException not thrown");
		} catch (AstProgramCompilationException $e) {
			$this->assertStringContainsString(
				"Duplicate Real value: '0.000' in type 'Real[1.3, 0, -3.14, 0.000]'",
				$e->getMessage()
			);
		}
	}

	public function testDuplicateStringSubsetValue(): void {
		try {
			$this->executeCodeSnippet(
				"null;",
				<<<NUT
					Dup = String['hi', 'ho', 'hi'];
				NUT
			);
			$this->fail("Expected AstProgramCompilationException not thrown");
		} catch (AstProgramCompilationException $e) {
			$this->assertStringContainsString(
				"Duplicate String value: 'hi' in type 'String['hi', 'ho', 'hi']'",
				$e->getMessage()
			);
		}
	}

}