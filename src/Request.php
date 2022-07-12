<?php

declare(strict_types=1);

namespace Verdient\WechatWork;

use chorus\InvalidCallException;
use chorus\InvalidParamException;

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
     * @var array 企业秘钥
     * @author Verdient。
     */
    public $corpSecrets = [];

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
     * @param string $agentId 代理编号
     * @return static
     * @author Verdient。
     */
    public function withToken($agentId)
    {
        $this->addQuery('access_token', $this->getAccessToken($agentId));
        return $this;
    }

    /**
     * 获取缓存路径
     * @param string $agentId 代理编号
     * @return string
     * @author Verdient。
     */
    protected function getCachePath($agentId)
    {
        return $this->tmpDir . DIRECTORY_SEPARATOR . $agentId . '_access_token';
    }

    /**
     * 获取企业秘钥
     * @param string $agentId 代理编号
     * @throws InvalidParamException
     * @return string
     * @author Verdient。
     */
    protected function getCorpSecret($agentId)
    {
        if (!isset($this->corpSecrets[$agentId])) {
            throw new InvalidParamException('Unknown Agent ID ' . $agentId);
        }
        return $this->corpSecrets[$agentId];
    }

    /**
     * 从缓存中获取授权秘钥
     * @param string $agentId 代理编号
     * @return string|false
     * @author Verdient。
     */
    protected function getAccessTokenFromCache($agentId)
    {
        $corpSecret = $this->getCorpSecret($agentId);
        $path = $this->getCachePath($agentId);
        if (!file_exists($path)) {
            return false;
        }
        if ($accessToken = @unserialize(file_get_contents($path))) {
            if ($accessToken instanceof AccessToken) {
                if ($accessToken->corpID == $this->corpID && $accessToken->corpSecret == $corpSecret && $accessToken->agentId == $agentId && !$accessToken->isExpired()) {
                    return $accessToken->accessToken;
                }
            }
        }
        @unlink($path);
        return false;
    }

    /**
     * 设置授权秘钥缓存
     * @param string $agentId 代理编号
     * @param string $accessToken 授权秘钥
     * @param int $expiredAt 过期时间
     * @return bool
     * @author Verdient。
     */
    protected function setAccessTokenCache($agentId, $accessToken, $expiredAt)
    {
        $corpSecret = $this->getCorpSecret($agentId);
        $path = $this->getCachePath($agentId);
        if (!is_dir(dirname($path))) {
            mkdir($this->tmpDir, 0777, true);
        }
        $accessToken = new AccessToken($accessToken, $expiredAt, $agentId, $this->corpID, $corpSecret);
        return file_put_contents($path, serialize($accessToken)) !== false;
    }

    /**
     * 获取授权秘钥
     * @return string
     * @author Verdient。
     */
    public function getAccessToken($agentId)
    {
        if ($accessToken = $this->getAccessTokenFromCache($agentId)) {
            return $accessToken;
        }
        $request = new static;
        $response = $request
            ->setUrl($this->requestPath . '/gettoken')
            ->setMethod('POST')
            ->setBody([
                'corpid' => $this->corpID,
                'corpsecret' => $this->getCorpSecret($agentId)
            ])
            ->send();
        if (!$response->getIsOK()) {
            throw new InvalidCallException($response->getErrorMessage());
        }
        $data = $response->getData();
        $accessToken = $data['access_token'];
        $expiredAt = $data['expires_in'] + time() - 30;
        $this->setAccessTokenCache($agentId, $accessToken, $expiredAt);
        return $accessToken;
    }
}
