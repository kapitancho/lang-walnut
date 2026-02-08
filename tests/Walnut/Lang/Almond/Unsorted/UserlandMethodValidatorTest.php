<?php

namespace Walnut\Lang\Test\Almond\Unsorted;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class UserlandMethodValidatorTest extends CodeExecutionTestHelper {

	public function testUserlandMethodValidatorSignatureOk(): void {
		$result = $this->executeCodeSnippet("MyString('hello')->length", <<<NUT
			MyString := #String;
			MyString->length(=> Integer[999]) :: 999;
		NUT);
		$this->assertEquals('999', $result);
	}

	public function testUserlandMethodValidatorBadDependency(): void {
		$this->executeErrorCodeSnippet(
			'No implementation found for the requested type "[~MyString]".' ,
			"MyString('hello')->length",
		<<<NUT
			MyString := #String;
			MyString->length(=> Integer[999]) %% [~MyString] :: 999;
		NUT);
	}

	public function testUserlandMethodValidatorSignatureMismatchNative(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type: String",
			"null;", <<<NUT
			T1 = Integer<0..100>;
			T1->binaryPlus(^String => Integer) :: 3;
		NUT);
	}

	public function testUserlandMethodValidatorSignatureMismatchCustom(): void {
		$this->executeErrorCodeSnippet(
			"Error in method T1->x : the method x is already defined for T2 and therefore the signature ^Null => Real should be a subtype of ^Null => Integer",
			"null;", <<<NUT
			T1 = Integer<0..100>;
			T1->x(=> Real) :: 3.14;

			T2 = Integer<0..200>;
			T2->x(=> Integer) :: 1;
		NUT);
	}

	public function testUserlandMethodValidatorErrorCast(): void {
		$this->executeErrorCodeSnippet(
			'No implementation found for the requested type "[~T3]".',
			"T1->asT2;", <<<NUT
			T1 := ();
			T2 := ();
			T3 = String;
			T1 ==> T2 %% [~T3] :: T2;
		NUT);
	}

	public function testUserlandMethodValidatorErrorDependencyBuilder(): void {
		$this->executeErrorCodeSnippet(
			"Method 'err' is not defined for type 'DependencyContainer'.",
			"t();", <<<NUT
			T = String;
			==> T :: $->err;
		NUT, <<<NUT
			t = ^ %% [~T] :: null;
		NUT);
	}

	public function testUserlandMethodValidatorErrorValidator(): void {
		$this->executeErrorCodeSnippet(
			"Method 'err' is not defined for type 'Integer'.",
			"T1(1);", <<<NUT
			T1 := #Integer :: #->err;
		NUT);
	}

}