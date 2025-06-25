module $core:

/* global support */
Global := ();

/* constructor support */
Constructor := ();

/* number and range related atoms */
NotANumber := ();
MinusInfinity := ();
PlusInfinity := ();
InvalidRange := ();
IntegerRange := #[minValue: Integer|MinusInfinity, maxValue: Integer|PlusInfinity] @ InvalidRange ::
    ?whenTypeOf(#) is { `[minValue: Integer, maxValue: Integer]:
        ?when (#.minValue > #.maxValue) { => @InvalidRange }};
RealRange := #[minValue: Real|MinusInfinity, maxValue: Real|PlusInfinity] @ InvalidRange ::
    ?whenTypeOf(#) is { `[minValue: Real, maxValue: Real]:
        ?when (#.minValue > #.maxValue) { => @InvalidRange }};
LengthRange := #[minLength: Integer<0..>, maxLength: Integer<0..>|PlusInfinity] @ InvalidRange ::
    ?whenTypeOf(#) is { `[minLength: Integer<0..>, maxLength: Integer<0..>]:
        ?when (#.minLength > #.maxLength) { => @InvalidRange }};

PositiveInteger = Integer<1..>;
NonNegativeInteger = Integer<0..>;
NegativeInteger = Integer<..-1>;
NonPositiveInteger = Integer<..0>;
NonZeroInteger = Integer<(..0), (0..)>;

IntegerNumberIntervalEndpoint := [value: Integer, inclusive: Boolean];
IntegerNumberInterval := #[
    start: MinusInfinity|IntegerNumberIntervalEndpoint,
    end: PlusInfinity|IntegerNumberIntervalEndpoint
] @ InvalidRange :: ?whenTypeOf(#) is {
    `[start: IntegerNumberIntervalEndpoint, end: IntegerNumberIntervalEndpoint]:
        ?when (
            {#.start.value > #.end.value} ||
            {#.start.value == #.end.value && {!{#.start.inclusive} || !{#.end.inclusive}}}
        ) { => @InvalidRange }
};
IntegerNumberRange := [intervals: Array<IntegerNumberInterval, 1..>];

PositiveReal = Real<(0..)>;
NonNegativeReal = Real<[0..)>;
NegativeReal = Real<(..0)>;
NonPositiveReal = Real<(..0]>;
NonZeroReal = Real<(..0), (0..)>;

RealNumberIntervalEndpoint := [value: Real, inclusive: Boolean];
RealNumberInterval := #[
    start: MinusInfinity|RealNumberIntervalEndpoint,
    end: PlusInfinity|RealNumberIntervalEndpoint
] @ InvalidRange :: ?whenTypeOf(#) is {
    `[start: RealNumberIntervalEndpoint, end: RealNumberIntervalEndpoint]:
        ?when (
            {#.start.value > #.end.value} ||
            {#.start.value == #.end.value && {!{#.start.inclusive} || !{#.end.inclusive}}}
        ) { => @InvalidRange }
};
RealNumberRange := [intervals: Array<RealNumberInterval, 1..>];

NonEmptyString = String<1..>;

/* dependency container */
DependencyContainer := ();
DependencyContainerErrorType := (CircularDependency, Ambiguous, NotFound, UnsupportedType, ErrorWhileCreatingValue);
DependencyContainerError := [targetType: Type, errorOnType: Type, errorMessage: String, errorType: DependencyContainerErrorType];

/* json value */
InvalidJsonString := [value: String];
InvalidJsonValue := [value: Any];

/* arrays and maps */
IndexOutOfRange := [index: Integer];
MapItemNotFound := [key: String];
ItemNotFound := ();
SubstringNotInString := ();

/* casts */
CastNotAvailable := [from: Type, to: Type];

/* enumerations */
UnknownEnumerationValue := [enumeration: Type, value: String];

/* hydration */
HydrationError := [value: Any, hydrationPath: String, errorMessage: String];
HydrationError->errorMessage(=> String) :: ''->concatList[
    'Error in ', $hydrationPath, ': ', $errorMessage
];

/* IO etc. */
ExternalError := $[errorType: String, originalError: Any, errorMessage: String];

/* RegExp */
InvalidRegExp := #[expression: String];
RegExp := $String;
RegExpMatch := [match: String, groups: Array<String>];
NoRegExpMatch := ();

/* Random generator */
Random := ();

/* UUID */
InvalidUuid := [value: String];
Uuid := #String<36>;

/* Password handling */
PasswordString := #[value: String];