<?php

declare(strict_types=1);

namespace Verdient\YunExpress;

use Verdient\http\Response as HttpResponse;
use Verdient\HttpAPI\AbstractResponse;
use Verdient\HttpAPI\Result;

/**
 * 响应
 * @author Verdient。
 */
class Response extends AbstractResponse
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    protected function normailze(HttpResponse $response): Result
    {
        $result = new Result();
        $body = $response->getBody();
        $result->isOK = isset($body['success']) && $body['success'] === true;
        if ($result->isOK) {
            $result->data = $body;
        } else {
            $result->errorCode = $body['code'] ?? $response->getStatusCode();
            $result->errorMessage = $body['msg'] ?? $response->getStatusMessage();
        }
        return $result;
    }
}
