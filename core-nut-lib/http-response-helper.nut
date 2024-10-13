module http-response-helper %% http-core:

notFound = ^HttpRequest|String => HttpResponse :: {
    ?whenTypeOf(#) is {
        type{String}: [
            statusCode: 404,
            protocolVersion: HttpProtocolVersion.HTTP11,
            headers: [:]->withKeyValue[key: 'Content-Type', value: ['application/json']],
            body: [error: #]->jsonStringify
        ],
        type{HttpRequest}: [
            statusCode: 404,
            protocolVersion: HttpProtocolVersion.HTTP11,
            headers: [:]->withKeyValue[key: 'Content-Type', value: ['application/json']],
            body: [error: ''->concatList[
                'No route match found for ', #.method->asString, ' ', #.requestTarget
            ]]->jsonStringify
        ]
    }
};

badRequest = ^String => HttpResponse :: {
    [
        statusCode: 400,
        protocolVersion: HttpProtocolVersion.HTTP11,
        headers: [:]->withKeyValue[key: 'Content-Type', value: ['application/json']],
        body: [error: #]->jsonStringify
    ]
};

conflict = ^String => HttpResponse :: {
    [
        statusCode: 409,
        protocolVersion: HttpProtocolVersion.HTTP11,
        headers: [:]->withKeyValue[key: 'Content-Type', value: ['application/json']],
        body: [error: #]->jsonStringify
    ]
};

forbidden = ^String => HttpResponse :: {
    [
        statusCode: 403,
        protocolVersion: HttpProtocolVersion.HTTP11,
        headers: [:]->withKeyValue[key: 'Content-Type', value: ['application/json']],
        body: [error: #]->jsonStringify
    ]
};

internalServerError = ^String => HttpResponse :: {
    [
        statusCode: 500,
        protocolVersion: HttpProtocolVersion.HTTP11,
        headers: [:]->withKeyValue[key: 'Content-Type', value: ['application/json']],
        body: [error: #]->jsonStringify
    ]
};