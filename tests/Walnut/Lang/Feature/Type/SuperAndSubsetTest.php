<?php

namespace Walnut\Lang\Feature\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class SuperAndSubsetTest extends CodeExecutionTestHelper {

	public function testFullSubsetPart1(): void {
		// Return an IntPair and pass it to a function which expects IntPair
		$result = $this->executeCodeSnippet("useIntPair(getIntPair[1, 5]);", <<<NUT
			IntPair = <: [first: Integer, second: Integer];
			getIntPair = ^p: [first: Integer, second: Integer] => IntPair :: IntPair(p);
			useIntPair = ^p: IntPair => Integer :: p.first + p.second;
		NUT);
		$this->assertEquals("6", $result);
	}

	public function testFullSubsetPart2(): void {
		// Return an IntPair and pass it to a function which expects a record
		$result = $this->executeCodeSnippet("useIntPair(getIntPair[1, 5]);", <<<NUT
			IntPair = <: [first: Integer, second: Integer];
			getIntPair = ^p: [first: Integer, second: Integer] => IntPair :: IntPair(p);
			useIntPair = ^p: [first: Integer, second: Integer] => Integer :: p.first + p.second;
		NUT);
		$this->assertEquals("6", $result);
	}

	public function testFullSubsetPart3(): void {
		// Should fail: return a record which looks like IntPair and pass it to a function which expects IntPair
		$this->executeErrorCodeSnippet(
			"IntPair expected",
			"useIntPair(getIntPair[1, 5]);",
		<<<NUT
			IntPair = <: [first: Integer, second: Integer];
			getIntPair = ^p: [first: Integer, second: Integer] => [first: Integer, second: Integer] :: IntPair(p);
			useIntPair = ^p: IntPair => Integer :: p.first + p.second;
		NUT);
	}

	public function testFullSubsetPart4(): void {
		// Return an IntPair and pass it to a method of IntPair
		$result = $this->executeCodeSnippet("{getIntPair[1, 5]}->use;", <<<NUT
			IntPair = <: [first: Integer, second: Integer];
			getIntPair = ^p: [first: Integer, second: Integer] => IntPair :: IntPair(p);
			IntPair->use(=> Integer) :: \$first + \$second;
		NUT);
		$this->assertEquals("6", $result);
	}

	public function testFullSubsetPart5(): void {
		// Return an IntPair and pass it to a method of the main type
		$result = $this->executeCodeSnippet("{getIntPair[1, 5]}->use;", <<<NUT
			IntPairAlias = [first: Integer, second: Integer];
			IntPair = <: [first: Integer, second: Integer];
			getIntPair = ^p: [first: Integer, second: Integer] => IntPair :: IntPair(p);
			IntPairAlias->use(=> Integer) :: \$first + \$second;
		NUT);
		$this->assertEquals("6", $result);
	}

	public function testFullSubsetPart6(): void {
		// Should fail: Return a record which looks like IntPair and pass it to a method of IntPair
		$this->executeErrorCodeSnippet(
			"Cannot call method 'use' on type",
			"{getIntPair[1, 5]}->use;",
		<<<NUT
			IntPair = <: [first: Integer, second: Integer];
			getIntPair = ^p: [first: Integer, second: Integer] => [first: Integer, second: Integer] :: IntPair(p);
			IntPair->use(=> Integer) :: \$first + \$second;
		NUT);
	}

	public function testStrictSubsetPart1(): void {
		// Return a FromTo and pass it to a function which expects FromTo
		$result = $this->executeCodeSnippet("useFromTo(getFromTo=>invoke[1, 5]);", <<<NUT
			InvalidParameters = :[];
			FromTo = <: [from: Integer, to: Integer] @ InvalidParameters ::
				?whenIsTrue {#from > #to : => @InvalidParameters() };
			getFromTo = ^p: [from: Integer, to: Integer] => Result<FromTo, InvalidParameters> :: FromTo(p);
			useFromTo = ^p: FromTo => Integer :: p.to - p.from;
		NUT);
		$this->assertEquals("4", $result);
	}

	public function testStrictSubsetPart2(): void {
		// Return a FromTo and pass it to a function which expects a record
		$result = $this->executeCodeSnippet("useFromTo(getFromTo=>invoke[1, 5]);", <<<NUT
			InvalidParameters = :[];
			FromTo = <: [from: Integer, to: Integer] @ InvalidParameters ::
				?whenIsTrue {#from > #to : => @InvalidParameters() };
			getFromTo = ^p: [from: Integer, to: Integer] => Result<FromTo, InvalidParameters> :: FromTo(p);
			useFromTo = ^p: [from: Integer, to: Integer] => Integer :: p.to - p.from;
		NUT);
		$this->assertEquals("4", $result);
	}


	public function testStrictSubsetPart3(): void {
		// Should fail: return a record which looks like FromTo and pass it to a function which expects FromTo
		$this->executeErrorCodeSnippet(
			"FromTo expected",
			"useFromTo(getFromTo=>invoke[1, 5]);",
		<<<NUT
			InvalidParameters = :[];
			FromTo = <: [from: Integer, to: Integer] @ InvalidParameters ::
				?whenIsTrue {#from > #to : => @InvalidParameters() };
			getFromTo = ^p: [from: Integer, to: Integer] => Result<[from: Integer, to: Integer], InvalidParameters> :: FromTo(p);
			useFromTo = ^p: FromTo => Integer :: p.to - p.from;
		NUT);
	}

	public function testStrictSubsetPart4(): void {
		// Return a FromTo and pass it to a method of FromTo
		$result = $this->executeCodeSnippet("{getFromTo=>invoke[1, 5]}->use;", <<<NUT
			InvalidParameters = :[];
			FromTo = <: [from: Integer, to: Integer] @ InvalidParameters ::
				?whenIsTrue {#from > #to : => @InvalidParameters() };
			getFromTo = ^p: [from: Integer, to: Integer] => Result<FromTo, InvalidParameters> :: FromTo(p);
			FromTo->use(=> Integer) :: \$to - \$from;
		NUT);
		$this->assertEquals("4", $result);
	}

	public function testStrictSubsetPart5(): void {
		// Return a FromTo and pass it to a method of the main type
		$result = $this->executeCodeSnippet("{getFromTo=>invoke[1, 5]}->use;", <<<NUT
			InvalidParameters = :[];
			FromToAlias = [from: Integer, to: Integer];
			FromTo = <: [from: Integer, to: Integer] @ InvalidParameters ::
				?whenIsTrue {#from > #to : => @InvalidParameters() };
			getFromTo = ^p: [from: Integer, to: Integer] => Result<FromTo, InvalidParameters> :: FromTo(p);
			FromToAlias->use(=> Integer) :: \$to - \$from;
		NUT);
		$this->assertEquals("4", $result);
	}

	public function testStrictSubsetPart6(): void {
		// Should fail: Return a record which looks like FromTo and pass it to a method of FromTo
		$this->executeErrorCodeSnippet(
			"Cannot call method 'use' on type",
			"{getFromTo=>invoke[1, 5]}->use;",
		<<<NUT
			InvalidParameters = :[];
			FromTo = <: [from: Integer, to: Integer] @ InvalidParameters ::
				?whenIsTrue {#from > #to : => @InvalidParameters() };
			getFromTo = ^p: [from: Integer, to: Integer] => Result<[from: Integer, to: Integer], InvalidParameters> :: FromTo(p);
			FromTo->use(=> Integer) :: \$to - \$from;
		NUT);
	}

	public function testSubsetTypeCheck(): void {
		// Type information is lost because the function useIntPair expects the base type. Therefore, return false.
		// UPDATE: after the TypedValue refactor, the type information is preserved and true is returned instead.
		$result = $this->executeCodeSnippet("useIntPair(getIntPair[1, 5]);", <<<NUT
			IntPair = <: [first: Integer, second: Integer];
			getIntPair = ^p: [first: Integer, second: Integer] => IntPair :: IntPair(p);
			useIntPair = ^p: [first: Integer, second: Integer] => Boolean :: ?whenTypeOf(p) is {
				type{IntPair}: true,
				~: false
			};
		NUT);
		$this->assertEquals("true", $result);
	}

	public function testSubsetArrayUsage(): void {
		// Type information is lost because the Array item type is the base type.
		$result = $this->executeCodeSnippet("useIntPair[[], getIntPair[1, 5]];", <<<NUT
			IntPairAlias = [first: Integer, second: Integer];
			IntPair = <: [first: Integer, second: Integer];
			getIntPair = ^p: [first: Integer, second: Integer] => IntPair :: IntPair(p);
			useIntPair = ^[arr: Array<IntPairAlias>, el: IntPair] => Array<IntPairAlias> :: #arr->insertLast(#el);
		NUT);
		$this->assertEquals("[[first: 1, second: 5]]", $result);
	}

	public function testFullSupersetDirect(): void {
		$result = $this->executeCodeSnippet("useIntPair(getIntPair(IntPairType[1, 5]));", <<<NUT
			IntPairType = #[first: Integer, second: Integer];
			IntPair = [first: Integer, second: Integer];
			getIntPair = ^p: IntPairType => Shape<IntPair> :: p;
			useIntPair = ^p: Shape<IntPair> => Integer :: p->shape.first + p->shape.second;
		NUT);
		$this->assertEquals("6", $result);
	}

	public function testFullSupersetCast(): void {
		$result = $this->executeCodeSnippet("useIntPair(getIntPair(IntPairType[1, 5]));", <<<NUT
			IntPairType = #[a: Integer, b: Integer];
			IntPair = [first: Integer, second: Integer];
			IntPairType ==> IntPair :: [first: \$a, second: \$b];
			getIntPair = ^p: IntPairType => Shape<IntPair> :: p;
			useIntPair = ^p: Shape<IntPair> => Integer :: p->shape.first + p->shape.second;
		NUT);
		$this->assertEquals("6", $result);
	}

	public function testStrictSupersetCastOk(): void {
		// The cast may return an error and therefore only explicit usage is allowed.
		$result = $this->executeCodeSnippet("useIntPair(getIntPair=>invoke(IntPairType[1, 5]));", <<<NUT
			IntPairType = #[a: Integer, b: Integer];
			IntPair = [first: Integer, second: Integer];
			Incompatible = :[];
			IntPairType ==> IntPair :: [first: \$a, second: \$b];
			getIntPair = ^p: IntPairType => Result<Shape<IntPair>, Incompatible> :: p=>shaped(type{IntPair});
			useIntPair = ^p: Shape<IntPair> => Integer :: p->shape.first + p->shape.second;
		NUT);
		$this->assertEquals("6", $result);
	}

	public function testStrictSupersetCastError(): void {
		// The cast may return an error and therefore an implicit usage is not allowed.
		$this->executeErrorCodeSnippet(
			"expected a return value of type Shape<IntPair>, got IntPairType",
			"useIntPair(getIntPair(IntPairType[1, 5]));",
		<<<NUT
			IntPairType = #[a: Integer, b: Integer];
			IntPair = [first: Integer, second: Integer];
			Incompatible = :[];
			IntPairType ==> IntPair @ Incompatible :: [first: \$a, second: \$b];
			getIntPair = ^p: IntPairType => Shape<IntPair> :: p;
			useIntPair = ^p: Shape<IntPair> => Integer :: p->shape.first + p->shape.second;
		NUT);
	}

}