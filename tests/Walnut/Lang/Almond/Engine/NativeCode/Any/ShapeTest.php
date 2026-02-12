<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Any;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

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
		$result = $this->executeCodeSnippet("useIntPair(getIntPair(IntPairType![a: 1, b: 5])?);", <<<NUT
			IntPairType := [a: Integer, b: Integer];
			IntPair = [first: Integer, second: Integer];
			Incompatible := ();
			IntPairType ==> IntPair :: [first: \$a, second: \$b];
		NUT, <<<NUT
			getIntPair = ^p: IntPairType => Result<Shape<IntPair>, Incompatible> :: p->as(`IntPair)?;
			useIntPair = ^p: Shape<IntPair> => Integer :: p->shape(`IntPair).first + p->shape(`IntPair).second;
		NUT);
		$this->assertEquals("6", $result);
	}

	public function testStrictSupersetCastError(): void {
		// The cast may return an error and therefore an implicit usage is not allowed.
		$this->executeErrorCodeSnippet(
			"Function body return type 'IntPairType' is not compatible with declared return type 'Shape<IntPair>'.",
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

	public function testAsStringShape(): void {
		$result = $this->executeCodeSnippet("getReal()->shape(`Real)->asString;",
			valueDeclarations: "getReal = ^ => Shape<Real> :: 3.14;");
		$this->assertEquals("'3.14'", $result);
	}

	public function testAsStringShapeParam(): void {
		$result = $this->executeCodeSnippet("getValue(getReal());",
			valueDeclarations: "
				getValue = ^ r: Shape<Real> => Real :: r->shape(`Real);
				getReal = ^ => Shape<Real> :: 3.14;
			"
		);
		$this->assertEquals("3.14", $result);
	}

	public function testShapeWithCastError(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"getValue(getString());",
			valueDeclarations: "
				getString = ^ => String :: '7 days';
				getValue = ^ r: Shape<Real> => Real :: r->shape;
			"
		);
	}

	public function testAsStringShapeParamWithCast(): void {
		$result = $this->executeCodeSnippet("getReal()->shape(`Real)->ceil;",
			"
				MyReal := #[value: Real];
				MyReal ==> Real :: \$value;
			",
			"
				getValue = ^ r: Shape<Real> => Real :: r->shape(`Real);
				getReal = ^ => Shape<Real> :: MyReal[value: 3.14];
			"
		);
		$this->assertEquals("4", $result);
	}

	public function testShapeAsRefinedType(): void {
		$result = $this->executeCodeSnippet("useReal(getReal());",
			valueDeclarations: "
				useReal = ^p: Any => Any :: ?whenTypeOf(p) {
					type{Shape<Real>}: p->shape(`Real)->ceil,
					type{String}: p
				};
				getReal = ^ => Shape<Real> :: 3.14;
			"
		);
		$this->assertEquals("4", $result);
	}

	public function testX1(): void {
		$result = $this->executeCodeSnippet("useB(getA());", valueDeclarations: <<<NUT
			getA = ^ => [a: Real] :: [a: 3.14];	
			useB = ^v: Shape<[a: Real]> => Real :: v->shape(`[a: Real]).a;
		NUT);
		$this->assertEquals("3.14", $result);
	}

	public function testX2(): void {
		$this->executeErrorCodeSnippet(
			"Cannot convert value of type 'Shape<[a: Real]>' to shape '[a: Integer]'",
			"useB(getA());",
			valueDeclarations:  <<<NUT
			getA = ^ => [a: Real] :: [a: 3.14];	
			useB = ^v: Shape<[a: Real]> => Real :: v->shape(`[a: Integer]).a;
		NUT);
	}

	public function testX3(): void {
		$result = $this->executeCodeSnippet("useB(getA());", valueDeclarations: <<<NUT
			getA = ^ => [a: Integer] :: [a: 42];	
			useB = ^v: Shape<[a: Integer]> => Real :: v->shape(`[a: Real]).a;
		NUT);
		$this->assertEquals("42", $result);
	}

	public function testX4(): void {
		$result = $this->executeCodeSnippet("useB(getA());", valueDeclarations: <<<NUT
			getA = ^ => [a: Real] :: [a: 3.14];	
			useB = ^v: [a: Real] => Real :: v->shape(`[a: Real]).a;
		NUT);
		$this->assertEquals("3.14", $result);
	}

	public function testX5(): void {
		$this->executeErrorCodeSnippet(
			"Cannot convert value of type '[a: Real, ... String]' to shape '[a: Real]'",
			"useB(getA());", valueDeclarations: <<<NUT
			getA = ^ => [a: Real, ... String] :: [a: 3.14];	
			useB = ^v: [a: Real, ... String] => Real :: v->shape(`[a: Real]).a;
		NUT);
	}

	public function testX6(): void {
		$result = $this->executeCodeSnippet("useB(getA());", valueDeclarations: <<<NUT
			getA = ^ => [a: Real] :: [a: 3.14];	
			useB = ^v: [a: Real] => Real :: v->shape(`[a: Real, ... String]).a;
		NUT);
		$this->assertEquals("3.14", $result);
	}

	public function testX14(): void {
		$result = $this->executeCodeSnippet("useB(getA());", valueDeclarations: <<<NUT
			getA = ^ => Integer :: 42;	
			useB = ^v: Integer => Integer :: v->shape(`Real)->ceil;
		NUT);
		$this->assertEquals("42", $result);
	}

	public function testX15(): void {
		$result = $this->executeCodeSnippet("useB(getA());", valueDeclarations: <<<NUT
			getA = ^ => Real :: 3.14;	
			useB = ^v: Real => Array<Integer> :: v->shape(`Integer)->upTo(5);
		NUT);
		$this->assertEquals("[3, 4, 5]", $result);
	}

}