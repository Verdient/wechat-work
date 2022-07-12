<?php

declare(strict_types=1);

namespace Verdient\WechatWork;

use chorus\InvalidCallException;

/**
 * 请求
 * @author Verdient。
 */
class Request extends \Verdient\http\Request
{
    /**
     * @var string 企业编号
     * @author Verdient。
     */
    public $corpID = null;

    /**
     * @var string 企业秘钥
     * @author Verdient。
     */
    public $corpSecret = null;

    /**
     * @var string 临时文件夹
     * @author Verdient。
     */
    public $tmpDir = null;

    /**
     * @var string 请求路径
     * @author Verdient。
     */
    public $requestPath = null;

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function send(): Response
    {
        return new Response(parent::send());
    }

    /**
     * 附带令牌
     * @return static
     * @author Verdient。
     */
    public function withToken()
    {
        $this->addQuery('access_token', $this->getAccessToken());
        return $this;
    }

    /**
     * 获取授权秘钥
     * @return string
     * @author Verdient。
     */
    public function getAccessToken()
    {
        $path = $this->tmpDir . DIRECTORY_SEPARATOR . 'access_token';
        if (!is_dir($this->tmpDir)) {
            mkdir($this->tmpDir, 0777, true);
        }
        $accessToken = null;
        if (file_exists($path)) {
            try {
                $content = unserialize(file_get_contents($path));
                if (isset($content['accessToken']) && isset($content['corpID']) && isset($content['corpSecret']) && isset($content['expiredAt'])) {
                    if ($content['corpID'] == $this->corpID && $content['corpSecret'] == $this->corpSecret && $content['expiredAt'] > time()) {
                        $accessToken = $content['accessToken'];
                    }
                }
            } catch (\Throwable $e) {
                unlink($path);
            }
        }
        if (!$accessToken) {
            $request = new static;
            $response = $request
                ->setUrl($this->requestPath . '/gettoken')
                ->setMethod('POST')
                ->setBody([
                    'corpid' => $this->corpID,
                    'corpsecret' => $this->corpSecret
                ])
                ->send();
            if ($response->getIsOK()) {
                $data = $response->getData();
                $accessToken = $data['access_token'];
                file_put_contents($path, serialize([
                    'corpID' => $this->corpID,
                    'corpSecret' => $this->corpSecret,
                    'accessToken' => $accessToken,
                    'expiredAt' => $data['expires_in'] + time() - 30
                ]));
            } else {
                throw new InvalidCallException($response->getErrorMessage());
            }
        }
        return $accessToken;
    }
}
