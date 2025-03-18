<?php

namespace Walnut\Lang\Feature\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class SuperAndSubsetTest extends CodeExecutionTestHelper {

	public function testFullSupersetDirect(): void {
		$result = $this->executeCodeSnippet("useIntPair(getIntPair(IntPairType[1, 5]));", <<<NUT
			IntPairType = #[first: Integer, second: Integer];
			IntPair = [first: Integer, second: Integer];
			getIntPair = ^p: IntPairType => Shape<IntPair> :: p;
			useIntPair = ^p: Shape<IntPair> => Integer :: p->shape(`IntPair).first + p->shape(`IntPair).second;
		NUT);
		$this->assertEquals("6", $result);
	}

	public function testFullSupersetCast(): void {
		$result = $this->executeCodeSnippet("useIntPair(getIntPair(IntPairType[1, 5]));", <<<NUT
			IntPairType = #[a: Integer, b: Integer];
			IntPair = [first: Integer, second: Integer];
			IntPairType ==> IntPair :: [first: \$a, second: \$b];
			getIntPair = ^p: IntPairType => Shape<IntPair> :: p;
			useIntPair = ^p: Shape<IntPair> => Integer :: p->shape(`IntPair).first + p->shape(`IntPair).second;
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
			useIntPair = ^p: Shape<IntPair> => Integer :: p->shape(`IntPair).first + p->shape(`IntPair).second;
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