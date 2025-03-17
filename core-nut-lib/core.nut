module $core:

/* global support */
Global = :[];

/* constructor support */
Constructor = :[];

/* number and range related atoms */
NotANumber = :[];
MinusInfinity = :[];
PlusInfinity = :[];
InvalidRange = :[];
IntegerRange = #[minValue: Integer|MinusInfinity, maxValue: Integer|PlusInfinity] @ InvalidRange ::
    ?whenTypeOf(#) is { type[minValue: Integer, maxValue: Integer]:
        ?when (#.minValue > #.maxValue) { => @InvalidRange() }};
RealRange = #[minValue: Real|MinusInfinity, maxValue: Real|PlusInfinity] @ InvalidRange ::
    ?whenTypeOf(#) is { type[minValue: Real, maxValue: Real]:
        ?when (#.minValue > #.maxValue) { => @InvalidRange() }};
LengthRange = #[minLength: Integer<0..>, maxLength: Integer<0..>|PlusInfinity] @ InvalidRange ::
    ?whenTypeOf(#) is { type[minLength: Integer<0..>, maxLength: Integer<0..>]:
        ?when (#.minLength > #.maxLength) { => @InvalidRange() }};

/* dependency container */
DependencyContainer = :[];
DependencyContainerErrorType = :[CircularDependency, Ambiguous, NotFound, UnsupportedType, ErrorWhileCreatingValue];
DependencyContainerError = $[targetType: Type, errorOnType: Type, errorMessage: String, errorType: DependencyContainerErrorType];
DependencyContainerError->errorMessage(=> String) :: $errorMessage;
DependencyContainerError->targetType(=> Type) :: $targetType;
DependencyContainerError->errorType(=> DependencyContainerErrorType) :: $errorType;

/* json value */
InvalidJsonString = #[value: String];
InvalidJsonString->value(=> String) :: $value;
InvalidJsonValue = #[value: Any];

/* arrays and maps */
IndexOutOfRange = #[index: Integer];
MapItemNotFound = #[key: String];
ItemNotFound = :[];
SubstringNotInString = :[];

/* casts */
CastNotAvailable = #[from: Type, to: Type];

/* enumerations */
UnknownEnumerationValue = #[enumeration: Type, value: String];

/* hydration */
HydrationError = #[value: Any, hydrationPath: String, errorMessage: String];
HydrationError->errorMessage(=> String) :: ''->concatList[
    'Error in ', $hydrationPath, ': ', $errorMessage
];

/* IO etc. */
ExternalError = $[errorType: String, originalError: Any, errorMessage: String];

/* RegExp */
InvalidRegExp = #[expression: String];
RegExp = $String;
RegExpMatch = #[match: String, groups: Array<String>];
NoRegExpMatch = :[];

/* Random generator */
Random = :[];

/* UUID */
InvalidUuid = #[value: String];
Uuid = #String<36> @ InvalidUuid :: ?whenIsError(
    {RegExp('/[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}/')}
        ->matchString(#)
) { => @InvalidUuid[#] };

/* Password handling */
PasswordString = #[value: String];

