module http-response-builder %% http-core:

NotFound = ^HttpRequest|String => HttpResponse;
==> NotFound :: ^HttpRequest|String => HttpResponse :: {
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

BadRequest = ^String => HttpResponse;
==> BadRequest :: ^String => HttpResponse :: {
    [
        statusCode: 400,
        protocolVersion: HttpProtocolVersion.HTTP11,
        headers: [:]->withKeyValue[key: 'Content-Type', value: ['application/json']],
        body: [error: #]->jsonStringify
    ]
};

Conflict = ^String => HttpResponse;
==> Conflict :: ^String => HttpResponse :: {
    [
        statusCode: 409,
        protocolVersion: HttpProtocolVersion.HTTP11,
        headers: [:]->withKeyValue[key: 'Content-Type', value: ['application/json']],
        body: [error: #]->jsonStringify
    ]
};

Forbidden = ^String => HttpResponse;
==> Forbidden :: ^String => HttpResponse :: {
    [
        statusCode: 403,
        protocolVersion: HttpProtocolVersion.HTTP11,
        headers: [:]->withKeyValue[key: 'Content-Type', value: ['application/json']],
        body: [error: #]->jsonStringify
    ]
};

InternalServerError = ^String => HttpResponse;
==> InternalServerError :: ^String => HttpResponse :: {
    [
        statusCode: 500,
        protocolVersion: HttpProtocolVersion.HTTP11,
        headers: [:]->withKeyValue[key: 'Content-Type', value: ['application/json']],
        body: [error: #]->jsonStringify
    ]
};