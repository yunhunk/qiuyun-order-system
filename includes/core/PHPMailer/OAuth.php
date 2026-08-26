<?php

namespace core\PHPMailer;

use League\OAuth2\Client\Grant\RefreshToken;

/**
 * @see     http://oauth2-client.thephpleague.com
 * @author  Marcus Bointon (Synchro/coolbru) <phpmailer@synchromedia.co.uk>
 */
class OAuth
{

    protected $provider;

    protected $oauthToken;

    protected $oauthUserEmail = '';

    protected $oauthClientSecret = '';

    protected $oauthClientId = '';

    protected $oauthRefreshToken = '';

    public function __construct($options)
    {
        $this->provider          = $options['provider'];
        $this->oauthUserEmail    = $options['userName'];
        $this->oauthClientSecret = $options['clientSecret'];
        $this->oauthClientId     = $options['clientId'];
        $this->oauthRefreshToken = $options['refreshToken'];
    }

    /**
     * Get a new RefreshToken.
     *
     * @return RefreshToken
     */
    protected function getGrant()
    {
        return new RefreshToken();
    }

    protected function getToken()
    {
        return $this->provider->getAccessToken(
            $this->getGrant(),
            ['refresh_token' => $this->oauthRefreshToken]
        );
    }

    public function getOauth64()
    {
        // Get a new token if it's not available or has expired
        if (null === $this->oauthToken || $this->oauthToken->hasExpired()) {
            $this->oauthToken = $this->getToken();
        }

        return base64_encode(
            'user=' .
            $this->oauthUserEmail .
            "\001auth=Bearer " .
            $this->oauthToken .
            "\001\001"
        );
    }
}
