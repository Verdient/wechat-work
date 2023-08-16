<?php

declare(strict_types=1);

namespace Verdient\WechatWork;

use Verdient\HttpAPI\AbstractClient;

/**
 * 企业微信
 * @author Verdient。
 */
class WechatWork extends AbstractClient
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    public $protocol = 'https';

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public $host = 'qyapi.weixin.qq.com';

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public $routePrefix = 'cgi-bin';

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
     * @inheritdoc
     * @author Verdient。
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        if ($this->tmpDir === null) {
            $this->tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'wechat-work';
        }
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function request($path): Request
    {
        $this->request = Request::class;
        /**
         * @var Request
         */
        $request = parent::request($path);
        $request->corpID = $this->corpID;
        $request->corpSecrets = $this->corpSecrets;
        $request->tmpDir = $this->tmpDir;
        $request->requestPath = $this->getRequestPath();
        return $request;
    }
}
