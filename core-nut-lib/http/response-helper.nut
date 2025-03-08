module $http/response-helper %% $http/core:

HttpResponseHelper = :[];

HttpResponseHelper->notFound(^req: HttpRequest|String => HttpResponse) :: {
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

HttpResponseHelper->badRequest(^err: String => HttpResponse) :: {
    [
        statusCode: 400,
        protocolVersion: HttpProtocolVersion.HTTP11,
        headers: [:]->withKeyValue[key: 'Content-Type', value: ['application/json']],
        body: [error: err]->jsonStringify
    ]
};

HttpResponseHelper->conflict(^err: String => HttpResponse) :: {
    [
        statusCode: 409,
        protocolVersion: HttpProtocolVersion.HTTP11,
        headers: [:]->withKeyValue[key: 'Content-Type', value: ['application/json']],
        body: [error: err]->jsonStringify
    ]
};

HttpResponseHelper->forbidden(^err: String => HttpResponse) :: {
    [
        statusCode: 403,
        protocolVersion: HttpProtocolVersion.HTTP11,
        headers: [:]->withKeyValue[key: 'Content-Type', value: ['application/json']],
        body: [error: err]->jsonStringify
    ]
};

HttpResponseHelper->internalServerError(^err: String => HttpResponse) :: {
    [
        statusCode: 500,
        protocolVersion: HttpProtocolVersion.HTTP11,
        headers: [:]->withKeyValue[key: 'Content-Type', value: ['application/json']],
        body: [error: err]->jsonStringify
    ]
};