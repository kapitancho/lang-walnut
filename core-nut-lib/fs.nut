module $fs:

File = $[path: String];
File->path(=> String) :: $path;

CannotReadFile = $[file: File];
CannotReadFile->file(=> File) :: $file;
CannotReadFile ==> String :: 'Cannot read from file: '->concat($file->path);
CannotReadFile ==> ExternalError :: ExternalError[
    errorType: $->type->typeName,
    originalError: $,
    errorMessage: $->asString
];

CannotWriteFile = $[file: File];
CannotWriteFile->file(=> File) :: $file;
CannotWriteFile ==> String :: 'Cannot write to file: '->concat($file->path);
CannotWriteFile ==> ExternalError :: ExternalError[
    errorType: $->type->typeName,
    originalError: $,
    errorMessage: $->asString
];
