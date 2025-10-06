<?php

namespace Walnut\Lang\Feature\DI;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class DependencyContainerTest extends CodeExecutionTestHelper {

	public function testCycle(): void {
		$this->executeErrorCodeSnippet(
			"Error in the dependency builder",
			"f();",
		<<<NUT
		A := #Integer;
		B := #A;
		 ==> A %% B :: A(42);
		 ==> B %% A :: B(%);
		f = ^ %% B :: null;
	NUT);
	}

	public function testAlias(): void {
		$result = $this->executeCodeSnippet("f();", <<<NUT
		A := #Integer; ==> A :: A(42);
		B := #String; ==> B :: B('hello');
		C = [~A, ~B];
	NUT, <<<NUT
		f = ^ %% C :: %a->value + {%b->value->length};
	NUT);
		$this->assertEquals("47", $result);
	}

	public function testRecord(): void {
		$result = $this->executeCodeSnippet("f();", <<<NUT
		A := #Integer; ==> A :: A(42);
		B := #String; ==> B :: B('hello');
	NUT, <<<NUT
		f = ^ %% [~A, ~B] :: %a->value + {%b->value->length};
	NUT);
		$this->assertEquals("47", $result);
	}

	public function testRecordWithError(): void {
		$this->executeErrorCodeSnippet(
			"the dependency [~A, ~B] cannot be resolved: error returned while creating value (type: B)",
			"f();",
		<<<NUT
		A := #Integer; ==> A :: A(42);
		B := #String; ==> B @ Null :: @null;
		NUT,
		<<<NUT
		f = ^ %% [~A, ~B] :: %a->value + {%b->value->length};
		NUT);
	}

	public function testRecordCannotFindItem(): void {
		$this->executeErrorCodeSnippet(
			"the dependency [~A, ~B] cannot be resolved: error returned while creating value (type: B)",
			"f();",
		<<<NUT
		A := #Integer; ==> A :: A(42);
		B := #String;
		NUT,
		<<<NUT
		f = ^ %% [~A, ~B] :: %a->value + {%b->value->length};
		NUT);
	}

	public function testTuple(): void {
		$result = $this->executeCodeSnippet("f();", <<<NUT
		A := #Integer; ==> A :: A(42);
		B := #String; ==> B :: B('hello');
	NUT, <<<NUT
		f = ^ %% [A, B] :: %0->value + {%1->value->length};
	NUT);
		$this->assertEquals("47", $result);
	}

	public function testTupleError(): void {
		$this->executeErrorCodeSnippet(
			"the dependency [A, B] cannot be resolved: error returned while creating value (type: B)",
		"f();",
		<<<NUT
		A := #Integer; ==> A :: A(42);
		B := #String; ==> B @ Null :: @null;
		NUT,
		<<<NUT
		f = ^ %% [A, B] :: %0->value + {%1->value->length};
		NUT);
	}

	public function testTupleCannotFindItem(): void {
		$this->executeErrorCodeSnippet(
			"the dependency [A, B] cannot be resolved: error returned while creating value (type: B)",
		"f();",
		<<<NUT
		A := #Integer; ==> A :: A(42);
		B := #String;
		NUT,
		<<<NUT
		f = ^ %% [A, B] :: %0->value + {%1->value->length};
		NUT);
	}

	public function testData(): void {
		$result = $this->executeCodeSnippet("f();", <<<NUT
		A := Integer; ==> A :: A!42;
		B := A;
		NUT, <<<NUT
		f = ^ %% B :: %;
		NUT);
		$this->assertEquals("B!A!42", $result);
	}

	public function testDataWithError(): void {
		$this->executeErrorCodeSnippet(
			"error returned while creating value (type: B)",
			"f();",
		<<<NUT
		A := Integer; ==> A :: A!42;
		B := #A @ Null :: => @null;
		NUT,
		<<<NUT
		f = ^ %% B :: %;
		NUT);
	}

	public function testOpen(): void {
		$result = $this->executeCodeSnippet("f();", <<<NUT
		A := #Integer; ==> A :: A(42);
		B := #A;
		NUT, <<<NUT
		f = ^ %% B :: %;
		NUT);
		$this->assertEquals("B{A{42}}", $result);
	}

	public function testOpenWithError(): void {
		$this->executeErrorCodeSnippet(
			"error returned while creating value (type: B)",
			"f();",
		<<<NUT
		A := #Integer; ==> A :: A(42);
		B := #A @ Null :: => @null;
		NUT,
		<<<NUT
		f = ^ %% B :: %;
		NUT);
	}

	public function testOpenWithConstructor(): void {
		$result = $this->executeCodeSnippet("f();", <<<NUT
		A := #Integer; ==> A :: A(42);
		B := #Integer;
		B(v: A) :: v->value;
		NUT, <<<NUT
		f = ^ %% B :: %;
		NUT);
		$this->assertEquals("B{42}", $result);
	}

	public function testOpenWithConstructorError(): void {
		$this->executeErrorCodeSnippet(
			"error returned while creating value (type: B)",
			"f();",
		<<<NUT
		A := #Integer; ==> A :: A(42);
		B := #Integer;
		B(v: A) @ Null :: @null;
		NUT,
		<<<NUT
		f = ^ %% B :: %;
		NUT);
	}

	public function testSealed(): void {
		$result = $this->executeCodeSnippet("f();", <<<NUT
		A := \$Integer; ==> A :: A(42);
		B := \$A;
	NUT, <<<NUT
		f = ^ %% B :: %;
	NUT);
		$this->assertEquals("B{A{42}}", $result);
	}

	public function testSealedWithError(): void {
		$this->executeErrorCodeSnippet(
			"error returned while creating value (type: B)",
			"f();",
		<<<NUT
		A := \$Integer; ==> A :: A(42);
		B := \$A @ Null :: => @null;
		NUT,
		<<<NUT
		f = ^ %% B :: %;
		NUT);
	}

	public function testSealedWithConstructor(): void {
		$result = $this->executeCodeSnippet("f();", <<<NUT
		A := \$Integer; ==> A :: A(42);
		A->value(=> Integer) :: $$;
		B := \$Integer;
		B(v: A) :: v->value;
		NUT, <<<NUT
		f = ^ %% B :: %;
		NUT);
		$this->assertEquals("B{42}", $result);
	}

	public function testSealedWithConstructorError(): void {
		$this->executeErrorCodeSnippet(
			"error returned while creating value (type: B)",
			"f();",
		<<<NUT
		A := \$Integer; ==> A :: A(42);
		A->value(=> Integer) :: $$;
		B := \$Integer;
		B(v: A) @ Null :: @null;
		NUT,
		<<<NUT
		f = ^ %% B :: %;
		NUT);
	}

}