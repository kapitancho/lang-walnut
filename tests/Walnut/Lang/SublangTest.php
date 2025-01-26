<?php

namespace Walnut\Lang;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class SublangTest extends CodeExecutionTestHelper {

	private const string context = <<<CONTEXT
		A = :[];
		E = :[A, B, C];
		V <: Integer[0, 2];
		Z <: Integer<1..2>;
		U <: Z;
		S = $[v: Boolean];

		getValues = ^Null => Map :: [
			a: A[], n: null,
			ea: E.A, eb: E.B, ec: E.C, t: true, f: false,
			i0: 0, i1: 1, i2: 2,
			v0: V(0), v2: V(2), z1: Z(1), z2: Z(2), u1: U(Z(1)), u2: U(Z(2)),
			s0: '', s1: 'a', s2: 'b', s3: 'ab',
			sf: S[v: false], st: S[v: true]
		];
		getTypes = ^Null => Map<Type> :: type[
			a: A, n: Null,
			e: E, b: Boolean, t: True, f: False,
			es1: E[A], es2: E[B], es3: E[C],
			es4: E[A, B], es5: E[A, C], es6: E[B, C],
			i: Integer, 
			i0: Integer<0..0>, i1: Integer<1..1>, i2: Integer<2..2>, 
			i01: Integer<0..1>, i02: Integer<0..2>, i12: Integer<1..2>,
			is0: Integer[0], is1: Integer[1], is2: Integer[2],
			is01: Integer[0, 1], is02: Integer[0, 2], is12: Integer[1, 2],
			s: String, 
			s0: String<0..0>, s1: String<1..1>, s2: String<2..2>, 
			s01: String<0..1>, s02: String<0..2>, s12: String<1..2>,
			ss: String[''], ssa: String['a'], ssb: String['b'], ssc: String['ab'],
			ssea: String['', 'a'], sseb: String['', 'b'], ssec: String['', 'ab'],
			ssab: String['a', 'b'], ssac: String['a', 'ab'], ssbc: String['b', 'ab'],
			sseab: String['', 'a', 'b'], sseac: String['', 'a', 'ab'], 
			ssebc: String['', 'b', 'ab'], ssabc: String['a', 'b', 'ab'],
			sseabc: String['', 'a', 'b', 'ab'],
			v: V, z: Z, u: U,
			s: S
		]->itemTypes;
	CONTEXT;

	public function test1(): void {
		$result = $this->executeCodeSnippet(<<<CODE
			vx = getValues(); 
			tx = getTypes();
			vx->map(^Any => Any :: {
				val = #;
				tx->map(^Type => Any :: {
					t = #;
					[val->printed, '<:', t->printed, '=', val->isOfType(t)->printed]->combineAsString(' ')
				})
			}); 
		CODE,
			self::context
		);
		//Not really needed for now
		$this->assertNotEquals('1', $result);
	}
}