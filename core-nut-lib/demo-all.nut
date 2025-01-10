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

/* Subtypes */
MySubtype <: String @ MyAtom :: null;

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

/* Variables */
a = 1;
b = ^MyAlias => Integer :: # + a;
c = type{String};

main = ^Array<String> => String :: [
    getAllExpressions(),
    a, b, b(2), c, main->type,
    MyAtom[], MyAtom[]->myMethod('hi!'), MyEnum.Value1,
    MySealed[3.14, -2], MySubtype('Hello')
]->printed;