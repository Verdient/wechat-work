<?php
declare(strict_types=1);

namespace Verdient\WechatWork;

use Verdient\http\Response as HttpResponse;
use Verdient\HttpAPI\Result;

/**
 * 响应
 * @author Verdient。
 */
class Response extends \Verdient\HttpAPI\AbstractResponse
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    protected function normailze(HttpResponse $response): Result
    {
        $result = new Result;
        if($response->getStatusCode() === 200){
            $data = $response->getBody();
            if(isset($data['errcode']) && $data['errcode'] === 0){
                $result->data = $data;
                $result->isOK = true;
                return $result;
            }
        }
        $result->errorCode = $data['errcode'] ?? 0;
        $result->errorMessage = $data['errmsg'] ?? '';
        return $result;
    }
}