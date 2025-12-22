module $core:

/* constructor support */
Constructor := ();

/* number and range related atoms */
NotANumber := ();
MinusInfinity := ();
PlusInfinity := ();
NegativeValue := ();
InvalidIntegerRange := [min: Integer, max: Integer];
IntegerRange := #[minValue: Integer|MinusInfinity, maxValue: Integer|PlusInfinity] @ InvalidIntegerRange ::
    ?whenTypeOf(#) is { `[minValue: Integer, maxValue: Integer]:
        ?when (#.minValue > #.maxValue) { => @InvalidIntegerRange![min: #.minValue, max: #.maxValue] }};

InvalidRealRange := [min: Real, max: Real];
RealRange := #[minValue: Real|MinusInfinity, maxValue: Real|PlusInfinity] @ InvalidRealRange ::
    ?whenTypeOf(#) is { `[minValue: Real, maxValue: Real]:
        ?when (#.minValue > #.maxValue) { => @InvalidRealRange![min: #.minValue, max: #.maxValue] }};

InvalidLengthRange := [min: Integer<0..>, max: Integer<0..>];
LengthRange := #[minLength: Integer<0..>, maxLength: Integer<0..>|PlusInfinity] @ InvalidLengthRange ::
    ?whenTypeOf(#) is { `[minLength: Integer<0..>, maxLength: Integer<0..>]:
        ?when (#.minLength > #.maxLength) { => @InvalidLengthRange![min: #.minLength, max: #.maxLength] }};

PositiveInteger = Integer<1..>;
NonNegativeInteger = Integer<0..>;
NegativeInteger = Integer<..-1>;
NonPositiveInteger = Integer<..0>;
NonZeroInteger = Integer<(..0), (0..)>;

IntegerNumberIntervalEndpoint := [value: Integer, inclusive: Boolean];
IntegerNumberInterval := #[
    start: MinusInfinity|IntegerNumberIntervalEndpoint,
    end: PlusInfinity|IntegerNumberIntervalEndpoint
] @ InvalidIntegerRange :: ?whenTypeOf(#) is {
    `[start: IntegerNumberIntervalEndpoint, end: IntegerNumberIntervalEndpoint]:
        ?when (
            #.start.value > #.end.value ||
            #.start.value == #.end.value && {!#.start.inclusive || !#.end.inclusive}
        ) { => @InvalidIntegerRange![min: #.start.value, max: #.end.value] }
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
] @ InvalidRealRange :: ?whenTypeOf(#) is {
    `[start: RealNumberIntervalEndpoint, end: RealNumberIntervalEndpoint]:
        ?when (
            {#.start.value > #.end.value} ||
            {#.start.value == #.end.value && {{!#.start.inclusive} || {!#.end.inclusive}}}
        ) { => @InvalidRealRange![min: #.start.value, max: #.end.value] }
};
RealNumberRange := [intervals: Array<RealNumberInterval, 1..>];

NonEmptyString = String<1..>;
CannotFormatString := [values: Array<{String}>|Map<{String}>, format: String];

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
SliceNotInByteArray := ();

/* casts */
CastNotAvailable := [from: Type, to: Type];

/* functions */
InvocationError := [functionType: Type, parameterType: Type];

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
InvalidRegExp := [expression: String];
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

/* CLI */
CliEntryPoint = ^Array<String> => String;
