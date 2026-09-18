<?php

class OAuthTokenProvider
{
    public function getAccessToken()
    {
        return 'dummy-access-token';
    }
    public function getOauth64()
    {
        return base64_encode("user@example.com\0user@example.com\0dummy-access-token");
    }
}
