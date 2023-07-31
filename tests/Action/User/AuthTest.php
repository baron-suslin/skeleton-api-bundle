<?php

namespace App\Tests\Action\User;

use App\Entity\User;
use App\Tests\Action\AbstractActionTest;
use App\Tests\Action\ParamWrapper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthTest extends AbstractActionTest
{
    protected function getEntityName(): string
    {
        return User::class;
    }

    /**
     * @dataProvider authProvider
     */
    public function testAuth(int $status, array $credentials, array $contains = []): void
    {
        $this->processParamWrappers($credentials);

        $basicCredentials = [
            'grant_type' => 'password',
            'client_id' => 'test_client_identifier',
            'client_secret' => 'test_client_secret',
            'password' => '123',
        ];
        $credentials = \array_merge($basicCredentials, $credentials);

        static::$client->request(Request::METHOD_POST, static::$authUrl, $credentials);

        $response = static::$client->getResponse();

        static::assertResponseStatusCodeSame($status);

        foreach ($contains as $token) {
            static::assertStringContainsString($token, $response->getContent());
        }
    }

    public function authProvider(): array
    {
        return [
            // #0
            [
                Response::HTTP_OK,
                ['grant_type' => 'client_credentials'],
            ],
            // #1
            [
                Response::HTTP_OK,
                ['username' => new ParamWrapper(User::class, ['email' => 'admin@test.com'], 'email')],
            ],
            // #2
            [
                Response::HTTP_BAD_REQUEST,
                [
                    'username' => new ParamWrapper(User::class, ['email' => 'admin@test.com'], 'email'),
                    'password' => 'invalid',
                ],
                ['The user credentials were incorrect.'],
            ],
        ];
    }
}
