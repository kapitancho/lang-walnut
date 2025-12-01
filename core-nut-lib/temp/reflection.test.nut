module $temp/reflection-test %% $test/runner:

MyAtom := ();
MyEnum := (A, B, C);
MyEnumSubset = MyEnum[A, C];
MyAlias = String;
MyData := String;
MyOpen := #String;
MySealed := $String;

==> TestCases :: {
    [
        ^ => TestResult :: TestResult[
            name: 'Type test',
            expected: 'hello',
            actual: ^ :: {
                fn = ^ v: Type<String> :: 'hello'->hydrateAs(v);
                fn(`String<2..5>)
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<Type>->refType',
            expected: `String<2..5>,
            actual: ^ :: {
                fn = ^ t: Type<Type<String>> => Type<String> :: t->refType;
                fn(`Type<String<2..5>>)
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Shape test',
            expected: 'hello!',
            actual: ^ :: {
                fn = ^ v: Shape<String> => String :: v->shape(`String) + '!';
                fn('hello')
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<Shape>->refType',
            expected: `String<2>,
            actual: ^ :: {
                fn = ^ t: Type<Shape<String>> => Type<String> :: t->refType;
                fn(`{String<2>})
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<OptionalKey>->valueType',
            expected: `String<2>,
            actual: ^ :: {
                fn = ^ t: Type<[a: OptionalKey<String>]> => Type<String> :: t->itemTypes.a->valueType;
                fn(`[a: OptionalKey<String<2>>])
            }
        ],

        ^ => TestResult :: TestResult[
            name: 'Shape test',
            expected: 'hello',
            actual: ^ :: {
                fn = ^ t: MutableValue => Any :: t->value;
                fn(mutable{String, 'hello'})
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<Mutable>->valueType',
            expected: `String<2>,
            actual: ^ :: {
                fn = ^ t: Type<Mutable<String>> => Type<String> :: t->valueType;
                fn(`Mutable<String<2>>)
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<MutableValue>->valueType',
            expected: `String<2>,
            actual: ^ :: {
                fn = ^ t: Type<MutableValue> => Type :: t->valueType;
                fn(`Mutable<String<2>>)
            }
        ],

        ^ => TestResult :: TestResult[
            name: 'Result test',
            expected: 'hello',
            actual: ^ :: {
                fn = ^ v: Result<String, Integer> :: v ?? 'error';
                fn('hello')
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<Result>->returnType',
            expected: `String<2..5>,
            actual: ^ :: {
                fn = ^ t: Type<Result<String, Integer>> => Type<String> :: t->returnType;
                fn(`Result<String<2..5>, Integer<1..1000>>)
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<Result>->errorType',
            expected: `Integer<1..1000>,
            actual: ^ :: {
                fn = ^ t: Type<Result<String, Integer>> => Type<Integer> :: t->errorType;
                fn(`Result<String<2..5>, Integer<1..1000>>)
            }
        ],

        ^ => TestResult :: TestResult[
            name: 'Function test',
            expected: 'hello',
            actual: ^ :: {
                fn = ^ v: (^ => String) :: v();
                fn(^ => String<5> :: 'hello')
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<Function>->parameterType',
            expected: `String|Real,
            actual: ^ :: {
                fn = ^ t: Type<Function> => Type :: t->parameterType;
                fn(`^String|Real => Integer<1..1000>)
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<Function>->returnType',
            expected: `Integer<1..1000>,
            actual: ^ :: {
                fn = ^ t: Type<Function> => Type :: t->returnType;
                fn(`^String|Real => Integer<1..1000>)
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<^>->parameterType',
            expected: `String|Real,
            actual: ^ :: {
                fn = ^ t: Type<^String => Integer> => Type :: t->parameterType;
                fn(`^String|Real => Integer<1..1000>)
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<^>->returnType',
            expected: `Integer<1..1000>,
            actual: ^ :: {
                fn = ^ t: Type<^String => Integer> => Type<Integer> :: t->returnType;
                fn(`^String|Real => Integer<1..1000>)
            }
        ],

        ^ => TestResult :: TestResult[
            name: 'Type<Intersection>->itemTypes',
            expected: [`{MyAtom}, `Integer],
            actual: ^ :: {
                fn = ^ t: Type<Intersection> => Array<Type> :: t->itemTypes;
                fn(`{MyAtom}&Integer)
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<Union>->itemTypes',
            expected: [`{MyAtom}, `Integer],
            actual: ^ :: {
                fn = ^ t: Type<Union> => Array<Type> :: t->itemTypes;
                fn(`{MyAtom}|Integer)
            }
        ],

        ^ => TestResult :: TestResult[
            name: 'Type<Record>->itemTypes',
            expected: [a: `{MyAtom}, b: `Integer],
            actual: ^ :: {
                fn = ^ t: Type<Record> => Map<Type> :: t->itemTypes;
                fn(`[a: {MyAtom}, b: Integer, ...String])
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<Record>->restType',
            expected: `String,
            actual: ^ :: {
                fn = ^ t: Type<Record> => Type :: t->restType;
                fn(`[a: {MyAtom}, b: Integer, ...String])
            }
        ],

        ^ => TestResult :: TestResult[
            name: 'Type<Tuple>->itemTypes',
            expected: [`{MyAtom}, `Integer],
            actual: ^ :: {
                fn = ^ t: Type<Tuple> => Array<Type> :: t->itemTypes;
                fn(`[{MyAtom}, Integer, ...String])
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<Tuple>->restType',
            expected: `String,
            actual: ^ :: {
                fn = ^ t: Type<Tuple> => Type :: t->restType;
                fn(`[{MyAtom}, Integer, ...String])
            }
        ],


        ^ => TestResult :: TestResult[
            name: 'Type<Array>->itemType',
            expected: `String<2>,
            actual: ^ :: {
                fn = ^ t: Type<Array<String>> => Type<String> :: t->itemType;
                fn(`Array<String<2>, 2..5>)
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<Array>->minLength',
            expected: 2,
            actual: ^ :: {
                fn = ^ t: Type<Array<String>> => Integer<0..> :: t->minLength;
                fn(`Array<String<2>, 2..5>)
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<Array>->maxLength',
            expected: 5,
            actual: ^ :: {
                fn = ^ t: Type<Array<String>> => Integer<0..>|PlusInfinity :: t->maxLength;
                fn(`Array<String<2>, 2..5>)
            }
        ],

        ^ => TestResult :: TestResult[
            name: 'Type<Set>->itemType',
            expected: `String<2>,
            actual: ^ :: {
                fn = ^ t: Type<Set<String>> => Type<String> :: t->itemType;
                fn(`Set<String<2>, 2..5>)
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<Set>->minLength',
            expected: 2,
            actual: ^ :: {
                fn = ^ t: Type<Set<String>> => Integer<0..> :: t->minLength;
                fn(`Set<String<2>, 2..5>)
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<Set>->maxLength',
            expected: 5,
            actual: ^ :: {
                fn = ^ t: Type<Set<String>> => Integer<0..>|PlusInfinity :: t->maxLength;
                fn(`Set<String<2>, 2..5>)
            }
        ],

        ^ => TestResult :: TestResult[
            name: 'Type<Map>->keyType',
            expected: `String<1>,
            actual: ^ :: {
                fn = ^ t: Type<Map<String>> => Type<String> :: t->keyType;
                fn(`Map<String<1>:String<2>, 2..5>)
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<Map>->itemType',
            expected: `String<2>,
            actual: ^ :: {
                fn = ^ t: Type<Map<String>> => Type<String> :: t->itemType;
                fn(`Map<String<1>:String<2>, 2..5>)
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<Map>->minLength',
            expected: 2,
            actual: ^ :: {
                fn = ^ t: Type<Map<String>> => Integer<0..> :: t->minLength;
                fn(`Map<String<1>:String<2>, 2..5>)
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<Map>->maxLength',
            expected: 5,
            actual: ^ :: {
                fn = ^ t: Type<Map<String>> => Integer<0..>|PlusInfinity :: t->maxLength;
                fn(`Map<String<1>:String<2>, 2..5>)
            }
        ],

        ^ => TestResult :: TestResult[
            name: 'Type<Named>->typeName',
            expected: 'MyAlias',
            actual: ^ :: {
                fn = ^ t: Type<Named> => String<1..> :: t->typeName;
                fn(`MyAlias)
            }
        ],

        ^ => TestResult :: TestResult[
            name: 'Atom test',
            expected: 'MyAtom',
            actual: ^ :: {
                fn = ^ t: Atom => String<1..> :: t->type->typeName;
                fn(MyAtom)
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<Atom>->typeName',
            expected: 'MyAtom',
            actual: ^ :: {
                fn = ^ t: Type<Atom> => String<1..> :: t->typeName;
                fn(`MyAtom)
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<Atom>->atomValue',
            expected: MyAtom,
            actual: ^ :: {
                fn = ^ t: Type<Atom> => Any :: t->atomValue;
                fn(`MyAtom)
            }
        ],

        ^ => TestResult :: TestResult[
            name: 'Enumeration->enumeration',
            expected: `MyEnum,
            actual: ^ :: {
                fn = ^ t: Enumeration => Type<Enumeration> :: t->enumeration;
                fn(MyEnum.A)
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Enumeration->textValue',
            expected: 'A',
            actual: ^ :: {
                fn = ^ t: Enumeration => String<1..> :: t->textValue;
                fn(MyEnum.A)
            }
        ],

        ^ => TestResult :: TestResult[
            name: 'Type<Enumeration>->typeName',
            expected: 'MyEnum',
            actual: ^ :: {
                fn = ^ t: Type<Enumeration> => String<1..> :: t->typeName;
                fn(`MyEnum)
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<Enumeration>->values',
            expected: [MyEnum.A, MyEnum.B, MyEnum.C],
            actual: ^ :: {
                fn = ^ t: Type<Enumeration> => Array<Enumeration, 1..> :: t->values;
                fn(`MyEnum)
            }
        ],

        ^ => TestResult :: TestResult[
            name: 'Type<EnumerationSubset>->enumerationType',
            expected: `MyEnum,
            actual: ^ :: {
                fn = ^ t: Type<EnumerationSubset> => Type<Enumeration> :: t->enumerationType;
                fn(`MyEnumSubset)
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<EnumerationSubset>->values',
            expected: [MyEnum.A, MyEnum.C],
            actual: ^ :: {
                fn = ^ t: Type<EnumerationSubset> => Array<Enumeration, 1..> :: t->values;
                fn(`MyEnumSubset)
            }
        ],

        ^ => TestResult :: TestResult[
            name: 'Type<Alias>->typeName',
            expected: 'MyAlias',
            actual: ^ :: {
                fn = ^ t: Type<Alias> => String<1..> :: t->typeName;
                fn(`MyAlias)
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<Alias>->aliasedType',
            expected: `String,
            actual: ^ :: {
                fn = ^ t: Type<Alias> => Type :: t->aliasedType;
                fn(`MyAlias)
            }
        ],

        ^ => TestResult :: TestResult[
            name: 'Type<Data>->typeName',
            expected: 'MyData',
            actual: ^ :: {
                fn = ^ t: Type<Data> => String<1..> :: t->typeName;
                fn(`MyData)
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<Data>->valueType',
            expected: `String,
            actual: ^ :: {
                fn = ^ t: Type<Data> => Type :: t->valueType;
                fn(`MyData)
            }
        ],

        ^ => TestResult :: TestResult[
            name: 'Type<Open>->typeName',
            expected: 'MyOpen',
            actual: ^ :: {
                fn = ^ t: Type<Open> => String<1..> :: t->typeName;
                fn(`MyOpen)
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<Open>->valueType',
            expected: `String,
            actual: ^ :: {
                fn = ^ t: Type<Open> => Type :: t->valueType;
                fn(`MyOpen)
            }
        ],

        ^ => TestResult :: TestResult[
            name: 'Type<Sealed>->typeName',
            expected: 'MySealed',
            actual: ^ :: {
                fn = ^ t: Type<Sealed> => String<1..> :: t->typeName;
                fn(`MySealed)
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<Sealed>->valueType',
            expected: `String,
            actual: ^ :: {
                fn = ^ t: Type<Sealed> => Type :: t->valueType;
                fn(`MySealed)
            }
        ],

        ^ => TestResult :: TestResult[
            name: 'Type<Integer>->numberRange',
            expected: :: IntegerNumberRange![intervals: [
                ?noError(IntegerNumberInterval[
                    start: IntegerNumberIntervalEndpoint![value: -3, inclusive: true],
                    end: IntegerNumberIntervalEndpoint![value: 42, inclusive: false]
                ])
            ]],
            actual: ^ :: {
                fn = ^ t: Type<Integer> => IntegerNumberRange :: t->numberRange;
                fn(`Integer<[-3..42)>)
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<Integer>->minValue',
            expected: -3,
            actual: ^ :: {
                fn = ^ t: Type<Integer> => Integer|MinusInfinity :: t->minValue;
                fn(`Integer<[-3..42)>)
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<Integer>->maxValue',
            expected: 42,
            actual: ^ :: {
                fn = ^ t: Type<Integer> => Integer|PlusInfinity :: t->maxValue;
                fn(`Integer<[-3..42)>)
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<IntegerSubset>->values',
            expected: [-3, 42],
            actual: ^ :: {
                fn = ^ t: Type<IntegerSubset> => Array<Integer, 1..> :: t->values;
                fn(`Integer[-3, 42])
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<Real>->numberRange',
            expected: :: RealNumberRange![intervals: [
                ?noError(RealNumberInterval[
                    start: RealNumberIntervalEndpoint![value: -3.14, inclusive: true],
                    end: RealNumberIntervalEndpoint![value: 42, inclusive: false]
                ])
            ]],
            actual: ^ :: {
                fn = ^ t: Type<Real> => RealNumberRange :: t->numberRange;
                fn(`Real<[-3.14..42)>)
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<Real>->minValue',
            expected: -3.14,
            actual: ^ :: {
                fn = ^ t: Type<Real> => Real|MinusInfinity :: t->minValue;
                fn(`Real<[-3.14..42)>)
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<Real>->maxValue',
            expected: 42,
            actual: ^ :: {
                fn = ^ t: Type<Real> => Real|PlusInfinity :: t->maxValue;
                fn(`Real<[-3.14..42)>)
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<RealSubset>->values',
            expected: [-3.14, 42],
            actual: ^ :: {
                fn = ^ t: Type<RealSubset> => Array<Real, 1..> :: t->values;
                fn(`Real[-3.14, 42])
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<String>->minLength',
            expected: 3,
            actual: ^ :: {
                fn = ^ t: Type<String> => Integer :: t->minLength;
                fn(`String<3..42>)
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<String>->maxLength',
            expected: 42,
            actual: ^ :: {
                fn = ^ t: Type<String> => Integer|PlusInfinity :: t->maxLength;
                fn(`String<3..42>)
            }
        ],
        ^ => TestResult :: TestResult[
            name: 'Type<StringSubset>->values',
            expected: ['a', 'b'],
            actual: ^ :: {
                fn = ^ t: Type<StringSubset> => Array<String, 1..> :: t->values;
                fn(`String['a', 'b'])
            }
        ],

        ^ => TestResult :: TestResult[
            name: 'Smoke Test',
            expected: 'ok',
            actual: ^ :: 'ok'
        ]
    ]
};