<?php

namespace App\Tests\Action;

use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;

abstract class AbstractActionTest extends WebTestCase
{
    use RefreshDatabaseTrait;
    use MailerAssertionsTrait;

    protected string $url;

    protected static KernelBrowser $client;

    protected static string $authUrl = '/token';

    protected function setUp(): void
    {
        static::$client = static::createClient();
        static::$client->disableReboot();
    }

    public function getItem(array $credentials, string | int | ParamWrapper $id = null, int $statusCode = Response::HTTP_OK): array
    {
        $client = $this->authenticateClient($credentials);

        $this->processParamWrapper($id);

        if (null !== $id) {
            $url = \sprintf('%s/%s', $this->url, $id);
        }

        $client->request(Request::METHOD_GET, $url ?? $this->url);

        static::assertResponseStatusCodeSame($statusCode);

        return $this->getJsonResponse();
    }

    public function getList(
        array $credentials,
        string $url = null,
        array $filters = [],
        int $statusCode = Response::HTTP_OK,
        array $headers = null,
        array $contains = [],
    ): array {
        $client = $this->authenticateClient($credentials);

        $this->processParamWrappers($filters);

        $client->request(Request::METHOD_GET, $url ?? $this->url, [
            'query' => $filters,
            'headers' => $headers,
        ]);

        static::assertResponseStatusCodeSame($statusCode);

        foreach ($contains as $token) {
            static::assertStringContainsString($token, static::$client->getResponse()->getContent());
        }

        return $this->getJsonResponse();
    }

    public function createItem(
        array $credentials,
        array $data,
        string $url = null,
        int $statusCode = Response::HTTP_CREATED,
        array $contains = [],
    ): array {
        $client = $this->authenticateClient($credentials);

        $this->processParamWrappers($data);

        $client->request(Request::METHOD_POST, $url ?? $this->url, $data);

        static::assertResponseStatusCodeSame($statusCode);

        $content = [];

        if (Response::HTTP_CREATED === $statusCode) {
            $content = $this->getJsonResponse();
        }

        foreach ($contains as $token) {
            static::assertStringContainsString($token, static::$client->getResponse()->getContent());
        }

        return $content;
    }

    public function updateItem(
        array $credentials,
        array $data,
        string | int | ParamWrapper $id = null,
        string $url = null,
        int $statusCode = Response::HTTP_OK,
        array $contains = [],
        string $method = Request::METHOD_PATCH,
    ): array {
        $client = $this->authenticateClient($credentials);
        $contentType = 'application/merge-patch+json';

        if (Request::METHOD_PUT === $method) {
            $contentType = 'application/json';
        }

        $headers = ['Content-type' => $contentType];

        $this->processParamWrapper($id);
        $this->processParamWrappers($data);

        if (null !== $id) {
            $url = \sprintf('%s/%s', $url ?? $this->url, $id);
        }

        $client->request($method, $url, $data, [], $headers);

        static::assertResponseStatusCodeSame($statusCode);

        $content = [];

        if (Response::HTTP_OK === $statusCode) {
            $content = $this->getJsonResponse();
        }

        foreach ($contains as $token) {
            static::assertStringContainsString($token, static::$client->getResponse()->getContent());
        }

        return $content;
    }

    public function transitItem(
        array $credentials,
        array $data,
        string | int | ParamWrapper $id = null,
        string $url = null,
        int $statusCode = Response::HTTP_OK,
        array $contains = [],
    ): array {
        $client = $this->authenticateClient($credentials);
        $headers = ['Content-type' => 'application/merge-patch+json'];

        $this->processParamWrapper($id);

        $url = \sprintf('%s/%s/transit', $url ?? $this->url, $id ?? $this->getObjectOf($this->getEntityName()));

        $client->request(Request::METHOD_PATCH, $url, [
            'json' => $data,
            'headers' => $headers,
        ]);

        static::assertResponseStatusCodeSame($statusCode);

        foreach ($contains as $token) {
            static::assertStringContainsString($token, static::$client->getResponse()->getContent());
        }

        return $this->getJsonResponse();
    }

    public function deleteItem(
        array $credentials,
        $id = null,
        int $statusCode = Response::HTTP_NO_CONTENT,
        bool $softDelete = false,
        string $url = null,
    ): array {
        $client = $this->authenticateClient($credentials);

        $this->processParamWrapper($id);

        $url = \sprintf('%s/%s', $url ?? $this->url, $id ?? $this->getObjectOf($this->getEntityName()));

        $client->request(Request::METHOD_DELETE, $url);

        static::assertResponseStatusCodeSame($statusCode);

        $criteria = ['id' => $id];

        $result = $this->getObjectOf($this->getEntityName(), $criteria, false);

        if (Response::HTTP_NO_CONTENT === $statusCode && !$softDelete) {
            static::assertNull($result);
        } else {
            static::assertNotNull($result);
        }

        $content = [];

        if (Response::HTTP_NO_CONTENT !== $statusCode) {
            $content = $this->getJsonResponse();
        }

        return $content;
    }

    protected function getObjectOf(string $class, ?array $criteria = null, bool $fail = true): ?object
    {
        $object = static::getContainer()
            ->get('doctrine.orm.default_entity_manager')
            ->getRepository($class)
            ->findOneBy($criteria)
        ;

        if (null === $object && $fail) {
            static::fail('test object ('.$class.') not found: '.\print_r($criteria, true));
        }

        return $object;
    }

    protected function getObjectsOf(string $class, ?array $criteria = null, bool $fail = true): array
    {
        $objects = static::getContainer()
            ->get('doctrine.orm.default_entity_manager')
            ->getRepository($class)
            ->findBy($criteria)
        ;

        if ($fail && 0 === \count($objects)) {
            static::fail('test objects ('.$class.') not found: '.\print_r($criteria, true));
        }

        return $objects;
    }

    protected function authenticateClient(array $credentials): KernelBrowser
    {
        $this->processParamWrappers($credentials);

        if (empty($credentials)) {
            return static::$client;
        }

        $basicCredentials = [
            'grant_type' => 'password',
            'client_id' => 'test_client_identifier',
            'client_secret' => 'test_client_secret',
            'password' => '123',
        ];
        $credentials = \array_merge($basicCredentials, $credentials);

        static::$client->request(Request::METHOD_POST, static::$authUrl, $credentials);

        $response = static::$client->getResponse();
        $content = \json_decode($response->getContent(), true);

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            static::fail($content['message']);
        }

        static::$client->setServerParameters(['HTTP_Authorization' => \sprintf('Bearer %s', $content['access_token'])]);

        return static::$client;
    }

    protected function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    protected function processParamWrappers(array &$params): void
    {
        \array_walk_recursive($params, fn (&$value) => $this->processParamWrapper($value));
    }

    /**
     * @param mixed $value
     */
    protected function processParamWrapper(mixed &$value): void
    {
        if ($value instanceof ParamWrapper) {
            $criteria = $value->getCriteria();
            $this->processParamWrappers($criteria);

            $entity = $this->getObjectOf($value->getClass(), $criteria);

            $path = $value->getPath();
            $result = [];

            foreach ((array) $path as $key => $singlePath) {
                $result[$key] = PropertyAccess::createPropertyAccessorBuilder()
                    ->enableExceptionOnInvalidIndex()
                    ->getPropertyAccessor()
                    ->getValue($entity, $singlePath)
                ;
            }

            $value = count($result) > 1 ? $result : $result[0];
        }
    }

    public function assertArrayHasKeyRecursive($result, $contains): void
    {
        foreach ((array) $contains as $key => $token) {
            if (\is_array($token)) {
                static::assertArrayHasKey($key, $result);
                $this->assertArrayHasKeyRecursive($result[$key], $token);
            } else {
                static::assertArrayHasKey($token, $result);
            }
        }
    }

    public function objectToStatus(ParamWrapper|int $id, string $status): void
    {
        static::processParamWrapper($id);
        $class = static::getEntityName();

        $object = static::getObjectOf($class, ['id' => $id]);
        $object->setStatus($status);

        static::getContainer()
            ->get('doctrine')
            ->getManagerForClass($class)
            ->flush();
    }

    /**
     * Return name of model.
     */
    abstract protected function getEntityName(): string;

    protected function getJsonResponse()
    {
        $content = static::$client->getResponse()->getContent();

        if ($content) {
            static::assertJson($content);
            $content = json_decode($content, true);
        }

        return $content;
    }
}
