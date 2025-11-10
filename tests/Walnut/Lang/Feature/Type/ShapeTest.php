<?php

namespace Walnut\Lang\Test\Feature\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ShapeTest extends CodeExecutionTestHelper {

	public function testShapeString(): void {
		$result = $this->executeCodeSnippet("{`Shape};");
		$this->assertEquals("type{Shape}", $result);
	}

	public function testFullSupersetDirectWithShape(): void {
		$result = $this->executeCodeSnippet("useIntPair(getIntPair(IntPairType![first: 1, second: 5]));", <<<NUT
			IntPairType := [first: Integer, second: Integer];
			IntPair = [first: Integer, second: Integer];
		NUT, <<<NUT
			getIntPair = ^p: IntPairType => Shape<IntPair> :: p;
			useIntPair = ^p: Shape<IntPair> => Integer :: p->shape(`IntPair).first + p->shape(`IntPair).second;
		NUT);
		$this->assertEquals("6", $result);
	}

	public function testFullSupersetDirectWithShapeExplicit(): void {
		$result = $this->executeCodeSnippet("useIntPair(getIntPair(IntPairType![first: 1, second: 5]));", <<<NUT
			IntPairType := [first: Integer, second: Integer];
			IntPair = [first: Integer, second: Integer];
		NUT, <<<NUT
			getIntPair = ^p: IntPairType => Shape<IntPair> :: p->shape(`IntPair);
			useIntPair = ^p: Shape<IntPair> => Integer :: p->shape(`IntPair).first + p->shape(`IntPair).second;
		NUT);
		$this->assertEquals("6", $result);
	}

	public function testFullSupersetDirectNoShape(): void {
		$result = $this->executeCodeSnippet("useIntPair(IntPairType![first: 1, second: 5]);", <<<NUT
			IntPairType := [first: Integer, second: Integer];
			IntPair = [first: Integer, second: Integer];
		NUT, <<<NUT
			useIntPair = ^p: IntPairType => Integer :: p->shape(`IntPair).first + p->shape(`IntPair).second;
		NUT);
		$this->assertEquals("6", $result);
	}

	public function testCannotUseOpenAsShape(): void {
		$this->executeErrorCodeSnippet(
			"Cannot convert value of type 'IntPairType' to shape 'IntPair'",
			"useIntPair(IntPairType[1, 5]);", <<<NUT
			IntPairType := #[first: Integer, second: Integer];
			IntPair = [first: Integer, second: Integer];
		NUT, <<<NUT
			useIntPair = ^p: IntPairType => Integer :: p->shape(`IntPair).first + p->shape(`IntPair).second;
		NUT);
	}

	public function testFullSupersetCastWithShape(): void {
		$result = $this->executeCodeSnippet("useIntPair(getIntPair(IntPairType![a: 1, b: 5]));", <<<NUT
			IntPairType := [a: Integer, b: Integer];
			IntPair = [first: Integer, second: Integer];
			IntPairType ==> IntPair :: [first: \$a, second: \$b];
		NUT, <<<NUT
			getIntPair = ^p: IntPairType => Shape<IntPair> :: p;
			useIntPair = ^p: Shape<IntPair> => Integer :: p->shape(`IntPair).first + p->shape(`IntPair).second;
		NUT);
		$this->assertEquals("6", $result);
	}

	public function testFullSupersetCastNoShape(): void {
		$result = $this->executeCodeSnippet("useIntPair(IntPairType![a: 1, b: 5]);", <<<NUT
			IntPairType := [a: Integer, b: Integer];
			IntPair = [first: Integer, second: Integer];
			IntPairType ==> IntPair :: [first: \$a, second: \$b];
		NUT, <<<NUT
			useIntPair = ^p: IntPairType => Integer :: p->shape(`IntPair).first + p->shape(`IntPair).second;
		NUT);
		$this->assertEquals("6", $result);
	}

	public function testStrictSupersetCastOk(): void {
		// The cast may return an error and therefore only explicit usage is allowed.
		$result = $this->executeCodeSnippet("useIntPair(getIntPair=>invoke(IntPairType![a: 1, b: 5]));", <<<NUT
			IntPairType := [a: Integer, b: Integer];
			IntPair = [first: Integer, second: Integer];
			Incompatible := ();
			IntPairType ==> IntPair :: [first: \$a, second: \$b];
		NUT, <<<NUT
			getIntPair = ^p: IntPairType => Result<Shape<IntPair>, Incompatible> :: p=>as(`IntPair);
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
			IntPairType := [a: Integer, b: Integer];
			IntPair = [first: Integer, second: Integer];
			Incompatible := ();
			IntPairType ==> IntPair @ Incompatible :: [first: \$a, second: \$b];
		NUT,
		<<<NUT
			getIntPair = ^p: IntPairType => Shape<IntPair> :: p;
			useIntPair = ^p: Shape<IntPair> => Integer :: p->shape.first + p->shape.second;
		NUT);
	}

	public function testStrictSupersetCastErrorShapeCall(): void {
		// The cast may return an error and therefore an implicit usage is not allowed.
		$this->executeErrorCodeSnippet(
			"Cannot convert value of type 'IntPairType' to shape 'IntPair' because the cast may return an error of type Incompatible",
			"useIntPair(getIntPair(IntPairType[1, 5]));",
		<<<NUT
			IntPairType := [a: Integer, b: Integer];
			IntPair = [first: Integer, second: Integer];
			Incompatible := ();
			IntPairType ==> IntPair @ Incompatible :: [first: \$a, second: \$b];
		NUT,
		<<<NUT
			getIntPair = ^p: IntPairType => Shape<IntPair> :: p->shape(`IntPair);
			useIntPair = ^p: Shape<IntPair> => Integer :: p->shape(`IntPair).first + p->shape(`IntPair).second;
		NUT);
	}

	public function testShapeIntersectionType(): void {
		$result = $this->executeCodeSnippet(
			"s(X);",
			"X := (); X ==> Integer :: 5; X ==> String :: 'hello';",
			"s = ^p: {Integer}&{String} => String :: p->shape(`String) + ' ' + p->shape(`Integer);"
		);
		$this->assertEquals("'hello 5'", $result);
	}

}