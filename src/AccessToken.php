<?php

declare(strict_types=1);

namespace Verdient\YunExpress;

/**
 * 授权秘钥
 * @author Verdient。
 */
class AccessToken
{
    /**
     * @param string $token 秘钥
     * @param int $expiredAt 过期时间
     * @author Verdient。
     */
    public function __construct(public string $token, public int $expiredAt)
    {
    }

    /**
     * 获取是否已过期
     * @author Verdient。
     */
    public function isExpired(): bool
    {
        return time() >= $this->expiredAt;
    }
}
