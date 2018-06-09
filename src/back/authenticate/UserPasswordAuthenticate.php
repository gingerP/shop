<?php

use Firebase\JWT\JWT;

include_once AuWebRoot . '/src/back/errors/NotSecuredConnectionError.php';
include_once AuWebRoot . '/src/back/errors/UnAuthorizedError.php';

class UserPasswordAuthenticate
{
    private $logger;
    const AUTHORIZATION_TOKEN_TYPE = 'authorization';
    const REFRESH_TOKEN_TYPE = 'refresh';

    function __construct()
    {
        $this->logger = Server::getInstance()->logger();
    }

    public function secure(&$request) {
        if (!$request->isSecure()) {
            throw new NotSecuredConnectionError();
        }
        return $this;
    }

    public function authenticate(&$request, &$response)
    {
        $headers = $request->headers();
        $authorization = $headers->get('Authorization');
        $parts = explode(' ', $authorization);
        try {
            if (count($parts) == 2) {
                $jwt = $parts[1];
                $publicKey = DBPreferencesType::getPreferenceValue(SettingsNames::JWT_PUBLIC_KEY);
                $decoded = json_decode(json_encode(JWT::decode($jwt, $publicKey, array('RS256'))), true);
                $jwtTtl = $decoded['exp'] - time();
                $this->logger->debug("JWT time to live $jwtTtl sec.");
                $Users = new DBUsersType();
                $userName = $decoded['user'];
                $user = $Users->getUserForName($userName);
                if (is_null($user) ) {
                    throw new UnAuthorizedError();
                }
                $request->user = $user;
            } else {
                throw new UnAuthorizedError();
            }
        } catch (Exception $error) {
            $this->logger->error($error);
            throw new UnAuthorizedError();
        }
    }

    public function validateToken($token)
    {
        try {
            $publicKey = DBPreferencesType::getPreferenceValue(SettingsNames::JWT_PUBLIC_KEY);
            $decoded = json_decode(json_encode(JWT::decode($token, $publicKey, array('RS256'))), true);
            $jwtTtl = $decoded['exp'] - time();
            $this->logger->debug("JWT time to live $jwtTtl sec.\n".json_encode($decoded));
            $Users = new DBUsersType();
            $userName = $decoded['user'];
            $user = $Users->getUserForName($userName);
            if (is_null($user)) {
                throw new UnAuthorizedError();
            }
            return $decoded;
        } catch (Exception $error) {
            $this->logger->error($error);
            throw new UnAuthorizedError();
        }
    }

    public function validateAuthToken($token)
    {
        $decoded = $this->validateToken($token);
        if (isset($decoded['token']) && $decoded['token'] === self::AUTHORIZATION_TOKEN_TYPE) {
            return $decoded;
        }
        throw new UnAuthorizedError();
    }


    public function validateRefreshToken($token)
    {
        $decoded = $this->validateToken($token);
        if (isset($decoded['token']) && $decoded['token'] === self::REFRESH_TOKEN_TYPE) {
            return $decoded;
        }
        throw new UnAuthorizedError();
    }

    public function token($userId)
    {
        $exp = DBPreferencesType::getPreferenceValue(SettingsNames::JWT_EXPIRES_IN, 60 * 5);
        $publicUrl = DBPreferencesType::getPreferenceValue(SettingsNames::PUBLIC_URL, '');
        $privateKey = DBPreferencesType::getPreferenceValue(SettingsNames::JWT_PRIVATE_KEY, '');
        $nowSeconds = time();
        $token = array(
            'iss' => $publicUrl,
            'iat' => $nowSeconds,
            'exp' => $nowSeconds + $exp,
            'user' => $userId,
            'token' => self::AUTHORIZATION_TOKEN_TYPE
        );

        return JWT::encode($token, $privateKey, 'RS256');
    }

    public function refreshToken($userId)
    {
        $exp = DBPreferencesType::getPreferenceValue(SettingsNames::JWT_REFRESH_TOKEN_EXPIRES_IN, 60 * 60);
        $publicUrl = DBPreferencesType::getPreferenceValue(SettingsNames::PUBLIC_URL, '');
        $privateKey = DBPreferencesType::getPreferenceValue(SettingsNames::JWT_PRIVATE_KEY, '');
        $nowSeconds = time();
        $token = array(
            'iss' => $publicUrl,
            'iat' => $nowSeconds,
            'exp' => $nowSeconds + $exp,
            'user' => $userId,
            'token' => self::REFRESH_TOKEN_TYPE
        );

        return JWT::encode($token, $privateKey, 'RS256');
    }
}
