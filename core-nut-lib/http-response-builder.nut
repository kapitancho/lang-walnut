module http-response-builder %% http-core:

NotFound = ^HttpRequest|String => HttpResponse;
==> NotFound :: ^req: HttpRequest|String => HttpResponse :: {
    ?whenTypeOf(req) is {
        type{String}: [
            statusCode: 404,
            protocolVersion: HttpProtocolVersion.HTTP11,
            headers: [:]->withKeyValue[key: 'Content-Type', value: ['application/json']],
            body: [error: req]->jsonStringify
        ],
        type{HttpRequest}: [
            statusCode: 404,
            protocolVersion: HttpProtocolVersion.HTTP11,
            headers: [:]->withKeyValue[key: 'Content-Type', value: ['application/json']],
            body: [error: ''->concatList[
                'No route match found for ', req.method->asString, ' ', req.requestTarget
            ]]->jsonStringify
        ]
    }
};

BadRequest = ^String => HttpResponse;
==> BadRequest :: ^err: String => HttpResponse :: {
    [
        statusCode: 400,
        protocolVersion: HttpProtocolVersion.HTTP11,
        headers: [:]->withKeyValue[key: 'Content-Type', value: ['application/json']],
        body: [error: err]->jsonStringify
    ]
};

Conflict = ^String => HttpResponse;
==> Conflict :: ^err: String => HttpResponse :: {
    [
        statusCode: 409,
        protocolVersion: HttpProtocolVersion.HTTP11,
        headers: [:]->withKeyValue[key: 'Content-Type', value: ['application/json']],
        body: [error: err]->jsonStringify
    ]
};

Forbidden = ^String => HttpResponse;
==> Forbidden :: ^err: String => HttpResponse :: {
    [
        statusCode: 403,
        protocolVersion: HttpProtocolVersion.HTTP11,
        headers: [:]->withKeyValue[key: 'Content-Type', value: ['application/json']],
        body: [error: err]->jsonStringify
    ]
};

InternalServerError = ^String => HttpResponse;
==> InternalServerError :: ^err: String => HttpResponse :: {
    [
        statusCode: 500,
        protocolVersion: HttpProtocolVersion.HTTP11,
        headers: [:]->withKeyValue[key: 'Content-Type', value: ['application/json']],
        body: [error: err]->jsonStringify
    ]
};