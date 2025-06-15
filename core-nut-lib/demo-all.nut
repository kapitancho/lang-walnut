module demo-all:

/* The global scope contains the following declarations: */

/* Type aliases: */
MyAlias = Integer;

/* Atoms */
MyAtom = :[];

/* Enumerations */
MyEnum = :[Value1, Value2, Value3];

/* Sealed types */
MyOpen = #[a: Integer, b: Integer] @ MyAtom :: null;
MyOpen0 = #[a: Integer, b: Integer];
MyOpen1 = #[a: Integer, b: Integer];
MyOpen1[a: Real, b: Real] :: [a: #a->asInteger, b: #b->asInteger];

/* Sealed types */
MySealed = $[a: Integer, b: Integer] @ MyAtom :: null;
MySealed0 = $[a: Integer, b: Integer];
MySealed1 = $[a: Integer, b: Integer];
MySealed1[a: Real, b: Real] :: [a: #a->asInteger, b: #b->asInteger];

/* Methods */
MyAtom->myMethod(^String => Integer) %% MyAtom :: #->length;

/* Constructors */
MyOpen[a: Real, b: Real] %% MyAtom :: [a: #a->asInteger, b: #b->asInteger];
MySealed[a: Real, b: Real] %% MyAtom :: [a: #a->asInteger, b: #b->asInteger];

functionName = ^Any => String :: 'function call result';
TypeName = #String;

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
    multiVariableAssignmentList: var{ variableName1, variableName2 } = [1, 2],
    multiVariableAssignmentDict: var{ key: variableName1, ~variableName2 } = [key: 1, variableName2: 2],
    variableName: variableName,
    methodCall: 'method call'->length,
    functionBody: ^Any => Any :: 'function body',
    mutable: mutable{String, 'mutable'},
    matchTrue: ?whenIsTrue { 'then 1': 'then 1', 'then 2': 'then 2', ~: 'default' },
    matchType: ?whenTypeOf ('type') is { `String['type']: 'then 1', `String['other type']: 'then 2', ~: 'default' },
    matchValue: ?whenValueOf ('value') is { 'value': 'then 1', 'other value': 'then 2', ~: 'default' },
    matchIfThenElse: ?when('condition') { 'then' } ~ { 'else' },
    matchIfThen: ?when('condition') { 'then' },
    matchIsErrorElse: ?whenIsError('condition') { 'then' } ~ { 'else' },
    matchIsError: ?whenIsError('condition') { 'then' },
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
    open: MyOpen0,
    sealed: MySealed0,
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

    shape: Shape<Integer>,
    shapeAny: Shape,
    proxy: Array<!MyOpen0>,

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
    anyOpen: Type<Open>,
    anySealed: Type<Sealed>,
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
    atom: MyAtom(),
    enumeration: MyEnum.Value1,
    enumerationSubset: MyEnum.Value1,
    open: MyOpen0[a: 3, b: -2],
    sealed: MySealed0[a: 3, b: -2],
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

    shape: 42,
    shapeAny: null,
    proxy: [MyOpen0[a: 3, b: -2]],

    any: -12,
    /* nothing: Nothing */
    /* optionalKeyType: ?Any,*/

    anyType: `String,
    anyReal: `Integer,

    anyIntegerSubset: `Integer[42, -2],
    anyRealSubset: `Real[1, 3.14],
    anyStringSubset: `String['a', ''],
    anyFunction: `^String => Integer,
    anyAtom: `MyAtom,
    anyEnumeration: `MyEnum,
    anyEnumerationSubset: `MyEnum[Value1, Value2],
    anyOpen: `MyOpen,
    anySealed: `MySealed,
    anyNamed: `MyAtom,
    anyAlias: `Alias,
    anyTuple: `[Integer, String],
    anyRecord: `[a: Integer, b: String],
    anyMutable: `Mutable<Real>,
    anyIntersection: `[a: String, ...] & [b: Integer, ... String],
    anyUnion: `Integer|MyAtom
];

getAllValues = ^Any => Any :: [
    atom: MyAtom(),
    booleanTrue: true,
    booleanFalse: false,
    enumeration: MyEnum.Value1,
    open: MyOpen[a: 3, b: -2],
    sealed: MySealed[a: 3, b: -2],
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
    type: `String,
    mutable: mutable{String, 'mutable'},
    error: @'error',
    function: ^Any => Any :: 'function body'
];

allConstants = [
    atom: MyAtom(),
    booleanTrue: true,
    booleanFalse: false,
    enumeration: MyEnum.Value1,
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
    type: `String,
    mutable: mutable{String, 'mutable'},
    error: @'error',
    function: ^Any => Any :: 'function body'
];

/* Variables */
a = 1;
b = ^MyAlias => Integer :: # + a;
c = `String;

main = ^Array<String> => String :: [
    allExpressions: getAllExpressions(),
    allTypesAndSampleValues: getAllTypes(getMatchingValuesForAllTypes()),
    allTypes: `AllTypes,
    allValues: getAllValues(),
    allConstants: allConstants
]->printed;