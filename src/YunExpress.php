<?php

namespace Verdient\YunExpress;

use Verdient\HttpAPI\AbstractClient;

/**
 * 云途物流
 * @author Verdient。
 */
class YunExpress extends AbstractClient
{
    /**
     * App编号
     * @author Verdient。
     */
    protected string $appId;

    /**
     * App秘钥
     * @author Verdient。
     */
    protected string $appSecret;

    /**
     * 来源标识
     * @author Verdient。
     */
    protected string $sourceKey;

    /**
     * 缓存文件夹
     * @author Verdient。
     */
    protected ?string $cacheDir;

    /**
     * 代理主机
     * @author Verdient。
     */
    protected string|null $proxyHost = null;

    /**
     * 代理端口
     * @author Verdient。
     */
    protected int|string|null $proxyPort = null;

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public $request = Request::class;

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function request($path): Request
    {
        /** @var Request */
        $request = parent::request($path);

        if ($this->proxyHost) {
            $request->setProxy($this->proxyHost, empty($this->proxyPort) ? null : intval($this->proxyPort));
        }

        $request->appId = $this->appId;
        $request->appSecret = $this->appSecret;
        $request->sourceKey = $this->sourceKey;
        $request->cacheDir = $this->cacheDir;
        return $request;
    }
}
