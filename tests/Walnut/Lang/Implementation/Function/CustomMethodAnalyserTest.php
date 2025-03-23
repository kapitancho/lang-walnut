<?php

namespace Walnut\Lang\Implementation\Function;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class CustomMethodAnalyserTest extends CodeExecutionTestHelper {

	public function testCustomMethodSignatureOk(): void {
		$result = $this->executeCodeSnippet("MyString('hello')->length", <<<NUT
			MyString = #String;
			MyString->length(=> Integer[999]) :: 999;
		NUT);
		$this->assertEquals('999', $result);
	}

	public function testCustomMethodBadDependency(): void {
		$this->executeErrorCodeSnippet(
			"the dependency [~MyString] cannot be resolved: error returned while creating value (type: MyString)",
			"MyString('hello')->length",
		<<<NUT
			MyString = #String;
			MyString->length(=> Integer[999]) %% [~MyString] :: 999;
		NUT);
	}

	public function testCustomMethodSignatureMismatchCustom(): void {
		$this->executeErrorCodeSnippet(
			"Error in method T1->x : the method x is already defined for T2 and therefore the signature ^Null => Real should be a subtype of ^Null => Integer",
			"null;", <<<NUT
			T1 = Integer<0..100>;
			T1->x(=> Real) :: 3.14;

			T2 = Integer<0..200>;
			T2->x(=> Integer) :: 1;
		NUT);
	}

	public function testCustomMethodErrorCast(): void {
		$this->executeErrorCodeSnippet(
			"Error in the cast T1 ==> T2 : the dependency [~T3] cannot be resolved: error returned while creating value (type: String)",
			"{T1()}->asT2;", <<<NUT
			T1 = :[];
			T2 = :[];
			T3 = String;
			T1 ==> T2 %% [~T3] :: T2();
		NUT);
	}

	public function testCustomMethodErrorDependencyBuilder(): void {
		$this->executeErrorCodeSnippet(
			"Error in the dependency builder of T : Cannot call method 'err' on type 'DependencyContainer'",
			"t();", <<<NUT
			T = String;
			==> T :: $->err;
			t = ^ %% [~T] :: null;
		NUT);
	}

	public function testCustomMethodErrorValidator(): void {
		$this->executeErrorCodeSnippet(
			"Error in the validator of T1 : Cannot call method 'err' on type 'Integer'",
			"T1(1);", <<<NUT
			T1 = #Integer :: #->err;
		NUT);
	}

}