<?php


namespace App\Authentication;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationFailureHandler as BaseHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthenticationFailureHandler extends BaseHandler
{
    const RESPONSE_CODE    = 400;
    const RESPONSE_MESSAGE = 'Bad credentials';

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = [
            'code'    => self::RESPONSE_CODE,
            'message' => self::RESPONSE_MESSAGE,
        ];
        // had to extend the handler because AuthenticationFailureEvent that
        // JWT handler dispatches have no method of setResponse
        //$response = parent::onAuthenticationFailure($request, $exception);
        //return $response;
        return new JsonResponse($data, self::RESPONSE_CODE);
    }

}