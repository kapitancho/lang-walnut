module demo-all:

/* The global scope contains the following declarations: */

/* Type aliases: */
MyAlias = Integer;

/* Atoms */
MyAtom = :[];

/* Enumerations */
MyEnum = :[Value1, Value2, Value3];

/* Sealed types */
MySealed = $[a: Integer, b: Integer] @ MyAtom :: null;
MySealed0 = $[a: Integer, b: Integer];
MySealed1 = $[a: Integer, b: Integer];
MySealed1[a: Real, b: Real] :: [a: #a->asInteger, b: #b->asInteger];

/* Subtypes */
MySubtype <: String @ MyAtom :: null;
MySubtype0 <: String;
MySubtype1 <: String;
MySubtype1(String) :: #->reverse;

/* Methods */
MyAtom->myMethod(^String => Integer) %% MyAtom :: #->length;

/* Constructors */
MySealed[a: Real, b: Real] %% MyAtom :: [a: #a->asInteger, b: #b->asInteger];

functionName = ^Any => String :: 'function call result';
TypeName <: String;

getAllExpressions = ^Any => Any :: [
    constant: 'constant',
    tuple: ['tuple', 1, 2, 3],
    record: [key: 'tuple', a: 1, b: 2, c: 3],
    set: ['set'; 1; 2; 3],
    sequence: {
        'evaluated'; 'evaluated and used'
    },
    return: ?when(0) { => 'return' },
    noError: ?noError('no error'),
    noExternalError: ?noExternalError('no external error'),
    variableAssignment: variableName = 'variable assignment',
    variableName: variableName,
    methodCall: 'method call'->length,
    functionBody: ^Any => Any :: 'function body',
    mutable: mutable{String, 'mutable'},
    matchTrue: ?whenIsTrue { 'then 1': 'then 1', 'then 2': 'then 2', ~: 'default' },
    matchType: ?whenTypeOf ('type') is { type{String['type']}: 'then 1', type{String['other type']}: 'then 2', ~: 'default' },
    matchValue: ?whenValueOf ('value') is { 'value': 'then 1', 'other value': 'then 2', ~: 'default' },
    matchIfThenElse: ?when('condition') { 'then' } ~ { 'else' },
    matchIfThen: ?when('condition') { 'then' },
    functionCall: functionName('parameter'),
    constructorCall: TypeName('parameter'),
    propertyAccess: [property: 'value'].property
];

AllTypes = [
    boolean: Boolean,
    true: True,
    false: False,
    null: Null,
    atom: MyAtom,
    enumeration: MyEnum,
    enumerationSubset: MyEnum[Value1, Value2],
    sealed: MySealed0,
    subtype: MySubtype0,
    integer: Integer,
    integerRange: Integer<1..10>,
    integerSubset: Integer[2, 15],
    real: Real,
    realRange: Real<3.14..10>,
    realSubset: Real[3.14, 10],
    string: String,
    stringLengthRange: String<5..10>,
    stringSubset: String['hi', 'hello'],

    array: Array,
    arrayWithType: Array<Integer>,
    arrayWithLengthRange: Array<1..10>,
    arrayWithTypeAndLengthRange: Array<String, 1..10>,

    map: Map,
    mapWithType: Map<Integer>,
    mapWithLengthRange: Map<1..10>,
    mapWithTypeAndLengthRange: Map<String, 1..10>,

    set: Set,
    setWithType: Set<Integer>,
    setWithLengthRange: Set<1..10>,
    setWithTypeAndLengthRange: Set<String, 1..10>,

    function: ^Any => Any,
    mutable: Mutable,
    result: Result,
    resultWithType: Result<Integer, String>,

    impure: Impure,
    impureWithType: Impure<Integer>,

    any: Any,
    /* nothing: Nothing */
    optionalKeyType: ?Any,

    anyType: Type,
    anyReal: Type<Real>,

    anyIntegerSubset: Type<IntegerSubset>,
    anyRealSubset: Type<RealSubset>,
    anyStringSubset: Type<StringSubset>,
    anyFunction: Type<Function>,
    anyAtom: Type<Atom>,
    anyEnumeration: Type<Enumeration>,
    anyEnumerationSubset: Type<EnumerationSubset>,
    anySealed: Type<Sealed>,
    anySubtype: Type<Subtype>,
    anyNamed: Type<Named>,
    anyAlias: Type<Alias>,
    anyTuple: Type<Tuple>,
    anyRecord: Type<Record>,
    anyMutable: Type<MutableValue>,
    anyIntersection: Type<Intersection>,
    anyUnion: Type<Union>
];

getAllTypes = ^AllTypes => Any :: #;

getMatchingValuesForAllTypes = ^Null => AllTypes :: [
    boolean: true,
    true: true,
    false: false,
    null: null,
    atom: MyAtom[],
    enumeration: MyEnum.Value1,
    enumerationSubset: MyEnum.Value1,
    sealed: MySealed0[a: 3, b: -2],
    subtype: MySubtype0('value'),
    integer: 5,
    integerRange: 5,
    integerSubset: 2,
    real: -7.3,
    realRange: 6.29,
    realSubset: 10,
    string: 'hello',
    stringLengthRange: 'hello',
    stringSubset: 'hello',

    array: [],
    arrayWithType: [1, 5],
    arrayWithLengthRange: [1, 'hello'],
    arrayWithTypeAndLengthRange: ['hello', 'world'],

    map: [:],
    mapWithType: [a: 3, b: 7],
    mapWithLengthRange: [a: 3, b: 'hello'],
    mapWithTypeAndLengthRange: [a: 'hello', b: 'world'],

    set: [;],
    setWithType: [5;],
    setWithLengthRange: [1; 3; 5],
    setWithTypeAndLengthRange: ['hello'; 'world'; 'hello'],

    function: ^Any => Any :: 'any',
    mutable: mutable{Any, 'hello'},
    result: 'result',
    resultWithType: @'error',

    impure: 'impure',
    impureWithType: @ExternalError[errorType: 'Error', originalError: 'Error', errorMessage: 'Error'],

    any: -12,
    /* nothing: Nothing */
    /* optionalKeyType: ?Any,*/

    anyType: type{String},
    anyReal: type{Integer},

    anyIntegerSubset: type{Integer[42, -2]},
    anyRealSubset: type{Real[1, 3.14]},
    anyStringSubset: type{String['a', '']},
    anyFunction: type{^String => Integer},
    anyAtom: type{MyAtom},
    anyEnumeration: type{MyEnum},
    anyEnumerationSubset: type{MyEnum[Value1, Value2]},
    anySealed: type{MySealed},
    anySubtype: type{MySubtype},
    anyNamed: type{MyAtom},
    anyAlias: type{Alias},
    anyTuple: type{[Integer, String]},
    anyRecord: type{[a: Integer, b: String]},
    anyMutable: type{Mutable<Real>},
    anyIntersection: type{[a: String, ...] & [b: Integer, ... String]},
    anyUnion: type{Integer|MyAtom}
];

getAllValues = ^Any => Any :: [
    atom: MyAtom[],
    booleanTrue: true,
    booleanFalse: false,
    enumeration: MyEnum.Value1,
    sealed: MySealed[a: 3, b: -2],
    subtype: MySubtype('value'),
    integer: 42,
    real: 3.14,
    string: 'hi!',
    null: null,
    emptyTuple: [],
    tuple: [1, 'hi!'],
    emptyRecord: [:],
    record: [a: 1, b: 'hi!'],
    emptySet: [;],
    set: [1; 'hi!'],
    type: type{String},
    mutable: mutable{String, 'mutable'},
    error: @'error',
    function: ^Any => Any :: 'function body'
];

/* Variables */
a = 1;
b = ^MyAlias => Integer :: # + a;
c = type{String};

main = ^Array<String> => String :: [
    allExpressions: getAllExpressions(),
    allTypesAndSampleValues: getAllTypes(getMatchingValuesForAllTypes()),
    allValues: getAllValues()
]->printed;