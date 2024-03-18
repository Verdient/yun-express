<?php

declare(strict_types=1);

namespace Verdient\YunExpress;

use Verdient\http\Request as HttpRequest;

/**
 * 请求
 * @author Verdient。
 */
class Request extends HttpRequest
{
    /**
     * App编号
     * @author Verdient。
     */
    public string $appId;

    /**
     * App秘钥
     * @author Verdient。
     */
    public string $appSecret;

    /**
     * 来源标识
     * @author Verdient。
     */
    public string $sourceKey;

    /**
     * 缓存文件夹
     * @author Verdient。
     */
    public ?string $cacheDir = null;

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function send(): Response
    {
        $timestamp = intval(microtime(true) * 1000);
        $this->addHeader('date', $timestamp);
        $this->addHeader('sign', $this->signature($timestamp));
        $this->addHeader('token', $this->getAccessToken());
        return new Response(parent::send());
    }

    /**
     * 获取访问秘钥
     * @return string
     * @author Verdient。
     */
    protected function getAccessToken()
    {
        if ($this->cacheDir) {
            if (!is_dir($this->cacheDir)) {
                mkdir($this->cacheDir, 0777, true);
            }
            $dir = $this->cacheDir;
        } else {
            $dir = sys_get_temp_dir();
        }
        $cacheKey = 'YunExpress-AccessToken-' . md5($this->appId) . '-' . md5($this->appSecret) . '-' . md5($this->sourceKey);
        $cachePath = $dir . DIRECTORY_SEPARATOR . $cacheKey;
        if (file_exists($cacheKey)) {
            $accessToken = unserialize(file_get_contents($cachePath));
            if ($accessToken && $accessToken instanceof AccessToken) {
                if ($accessToken->isExpired()) {
                    unlink($cachePath);
                } else {
                    return $accessToken->token;
                }
            }
        }

        $url = 'scheme://host:port/path';

        $port = $this->port;

        if ($this->scheme == 'http' && $this->port == 80) {
            $port = null;
        }

        if ($this->scheme == 'https' && $port == 443) {
            $port = null;
        }

        foreach ([
            'scheme' => $this->scheme,
            'host' => $this->host,
            ':port' => ($port ? ':' . $port : ''),
            '/path' => '/openapi/oauth2/token'
        ] as $name => $value) {
            $url = str_replace($name, $value, $url);
        }

        $request = new HttpRequest();
        $request->setUrl($url);
        $request->setMethod('POST');
        $request->setBody([
            'grantType' => 'client_credentials',
            'appId' => $this->appId,
            'appSecret' => $this->appSecret,
            'sourceKey' => $this->sourceKey
        ]);
        $res = $request->send();
        if ($res->getStatusCode() != 200) {
            $body = $res->getBody();
            throw new InvalidCredentialsException($body['message'] ?? 'Invalid credentials');
        }
        $body = $res->getBody();
        $accessToken = new AccessToken($body['accessToken'], time() + $body['expiresIn'] - 60);
        file_put_contents($cachePath, serialize($accessToken));
        return $accessToken->token;
    }

    /**
     * 生成签名
     * @param int $timestamp 时间戳
     * @return string
     * @author Verdient。
     */
    protected function signature($timestamp)
    {
        $content = $this->getContent();
        $params = [
            'body' => $content ?: null,
            'date' => $timestamp,
            'method' => $this->getMethod(),
            'uri' => parse_url($this->getUrl(), PHP_URL_PATH),
        ];
        $parts = [];
        foreach ($params as $name => $value) {
            if ($value) {
                $parts[] = $name . '=' . $value . '';
            }
        }
        $str = implode('&', $parts);
        return base64_encode(hash_hmac('SHA256', $str, $this->appSecret, true));
    }
}
