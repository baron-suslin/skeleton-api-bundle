<?php

namespace App\Tests\Action\User;

use App\Entity\User;
use App\Tests\Action\AbstractActionTest;
use App\Tests\Action\ParamWrapper;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UserTest.
 */
class UserTest extends AbstractActionTest
{
    protected string $url = '/api/users';
    protected string $meUrl = 'me';

    protected function getEntityName(): string
    {
        return User::class;
    }

    /**
     * @dataProvider listProvider
     */
    public function testList(int $status, array $credentials, array $contains = [], array $filters = []): void
    {
        $this->getList($credentials, $this->url, $filters, $status, [], $contains);
    }

    public static function listProvider(): array
    {
        return [
            // #0
            [
                Response::HTTP_OK,
                ['username' => new ParamWrapper(User::class, ['email' => 'admin@test.com'], 'email')],
                [
                    'admin@test.com',
                ],
            ],
        ];
    }

    /**
     * @dataProvider fetchProvider
     */
    public function testFetch(int $status, array $credentials, $id, array $contains = []): void
    {
        $response = $this->getItem($credentials, $id, $status);

        if (Response::HTTP_OK === $status) {
            foreach ($contains as $token) {
                static::assertArrayHasKey($token, $response);
            }
        }
    }

    public static function fetchProvider(): array
    {
        return [
            // #0
            [
                Response::HTTP_OK,
                ['username' => new ParamWrapper(User::class, ['email' => 'admin@test.com'], 'email')],
                new ParamWrapper(User::class, ['email' => 'admin@test.com']),
            ],
        ];
    }

//    /**
//     * @dataProvider fetchCurrentProvider
//     */
//    public function testFetchCurrent(int $status, array $credentials, string $email): void
//    {
//        $response = $this->getItem($credentials, $this->meUrl, $status);
//
//        if (Response::HTTP_OK === $status) {
//            static::assertEquals($email, $response['email']);
//        }
//    }
//
//    public static function fetchCurrentProvider(): array
//    {
//        return [
//            // #0
//            [
//                Response::HTTP_OK,
//                ['username' => new ParamWrapper(User::class, ['email' => 'admin@test.com'], 'email')],
//                'admin@test.com',
//            ],
//        ];
//    }

    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(array $credentials, array $data, int $status = Response::HTTP_CREATED, array $contains = [])
    {
        $this
            ->createItem($credentials, $data, null, $status, $contains)
        ;
    }

    public static function createDataProvider(): array
    {
        return [
            // #0
            [
                ['grant_type' => 'client_credentials'],
                [
                    'email' => 'test@test.com',
                    'plainPassword' => '123',
                ],
            ],
        ];
    }

    /**
     * @dataProvider updateDataProvider
     */
    public function testUpdate(array $credentials, $id, array $data, int $status, array $contains = [])
    {
        $this->updateItem($credentials, $data, $id, null, $status, $contains);
    }

    /**
     * @return array
     */
    public static function updateDataProvider(): array
    {
        return [
            // #0
            [
                ['username' => new ParamWrapper(User::class, ['email' => 'admin@test.com'], 'email')],
                new ParamWrapper(User::class, ['email' => 'admin@test.com']),
                [
                    'email' => 'new-email@test.com',
                ],
                Response::HTTP_OK,
            ],
        ];
    }

    /**
     * @dataProvider deleteProvider
     */
    public function testDelete(array $credentials, $id, int $status = Response::HTTP_NO_CONTENT) {
        $this->deleteItem($credentials, $id, $status);
    }

    /**
     * @return array[]
     */
    public static function deleteProvider(): array
    {
        return [
            // #0
            [
                ['username' => new ParamWrapper(User::class, ['email' => 'admin@test.com'], 'email')],
                new ParamWrapper(User::class, ['email' => 'admin@test.com']),
            ],
        ];
    }
}
