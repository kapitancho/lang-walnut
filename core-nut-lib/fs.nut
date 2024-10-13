module fs:

File = $[path: String];
File->path(^Null => String) :: $.path;

CannotReadFile = $[file: File];
CannotReadFile->file(^Null => File) :: $.file;
CannotReadFile ==> String :: 'Cannot read from file: '->concat($.file->path);
CannotReadFile ==> ExternalError :: ExternalError[
    errorType: $->type->typeName,
    originalError: $,
    errorMessage: $->asString
];

CannotWriteFile = $[file: File];
CannotWriteFile->file(^Null => File) :: $.file;
CannotWriteFile ==> String :: 'Cannot write to file: '->concat($.file->path);
CannotWriteFile ==> ExternalError :: ExternalError[
    errorType: $->type->typeName,
    originalError: $,
    errorMessage: $->asString
];
