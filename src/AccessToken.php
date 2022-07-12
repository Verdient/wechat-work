<?php

declare(strict_types=1);

namespace Verdient\WechatWork;

/**
 * 授权秘钥
 * @author Verdient。
 */
class AccessToken
{
    /**
     * @var string 代理编号
     * @author Verdient。
     */
    public $agentId;

    /**
     * @var string 企业编号
     * @author Verdient。
     */
    public $corpID;

    /**
     * @var string 企业秘钥
     * @author Verdient。
     */
    public $corpSecret;

    /**
     * @var string 授权秘钥
     * @author Verdient。
     */
    public $accessToken;

    /**
     * @var int 过期时间
     * @author Verdient。
     */
    public $expiredAt;

    /**
     * @param string $accessToken 授权秘钥
     * @param int $expiredAt 过期时间
     * @param string $agentId 代理编号
     * @param string $corpID 企业编号
     * @param string $corpSecret 企业秘钥
     * @author Verdient。
     */
    public function __construct($accessToken, $expiredAt, $agentId, $corpID, $corpSecret)
    {
        $this->accessToken = $accessToken;
        $this->expiredAt = $expiredAt;
        $this->agentId = $agentId;
        $this->corpID = $corpID;
        $this->corpSecret = $corpSecret;
    }

    /**
     * 获取是否已过期
     * @return bool
     * @author Verdient。
     */
    public function isExpired()
    {
        return time() >= $this->expiredAt;
    }
}
