<?php

namespace Modules\Brokers\Tests\Unit;

use Illuminate\Container\Container;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\Route;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;
use Mockery;
use Modules\Brokers\Http\Requests\GetGroupedUrlsRequest;
use PHPUnit\Framework\TestCase;

class GetGroupedUrlsRequestTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @param  array<string, string>  $routeParams
     */
    private function bindRoute(GetGroupedUrlsRequest $formRequest, array $routeParams): void
    {
        $route = Mockery::mock(Route::class);
        $route->allows('parameter')->andReturnUsing(function (string $name, mixed $default = null) use ($routeParams) {
            return $routeParams[$name] ?? $default;
        });
        $formRequest->setRouteResolver(fn () => $route);
    }

    private function container(): Container
    {
        $translator = new Translator(new ArrayLoader(), 'en');
        $container = new Container;
        $container->instance('translator', $translator);
        $container->singleton(ValidationFactory::class, fn () => new Factory($translator));

        return $container;
    }

    private function redirector(): Redirector
    {
        $urlGenerator = Mockery::mock(UrlGenerator::class);
        $urlGenerator->allows('previous')->andReturn('http://localhost/');
        $urlGenerator->allows('to')->andReturn('http://localhost/');
        $urlGenerator->allows('route')->andReturn('http://localhost/');
        $urlGenerator->allows('action')->andReturn('http://localhost/');

        $redirector = Mockery::mock(Redirector::class);
        $redirector->allows('getUrlGenerator')->andReturn($urlGenerator);

        return $redirector;
    }

    /**
     * @param  array<string, string>  $routeParams
     */
    private function makeFormRequest(array $routeParams, array $query = []): GetGroupedUrlsRequest
    {
        $base = Request::create('http://localhost/api/v1', 'GET', $query);
        /** @var GetGroupedUrlsRequest $formRequest */
        $formRequest = GetGroupedUrlsRequest::createFrom($base);
        $formRequest->setContainer($this->container());
        $formRequest->setRedirector($this->redirector());
        $this->bindRoute($formRequest, $routeParams);

        return $formRequest;
    }

    public function test_it_passes_validation_for_valid_account_type_and_entity_all(): void
    {
        $formRequest = $this->makeFormRequest([
            'broker_id' => '1',
            'entity_type' => 'account-type',
            'entity_id' => 'all',
        ]);

        $formRequest->validateResolved();

        $this->assertSame('en', $formRequest->input('language_code'));
    }

    public function test_it_passes_validation_for_numeric_entity_id(): void
    {
        $formRequest = $this->makeFormRequest([
            'broker_id' => '2',
            'entity_type' => 'account-type',
            'entity_id' => '42',
        ]);

        $formRequest->validateResolved();
        $this->assertSame('42', $formRequest->input('entity_id'));
    }

    public function test_it_rejects_invalid_entity_id(): void
    {
        $formRequest = $this->makeFormRequest([
            'broker_id' => '1',
            'entity_type' => 'account-type',
            'entity_id' => 'not-all-or-digits',
        ]);

        $this->expectException(ValidationException::class);
        $formRequest->validateResolved();
    }

    public function test_it_rejects_unknown_entity_type_slug(): void
    {
        $formRequest = $this->makeFormRequest([
            'broker_id' => '1',
            'entity_type' => 'not-a-real-model-slug-xyz',
            'entity_id' => '1',
        ]);

        $this->expectException(ValidationException::class);
        $formRequest->validateResolved();
    }

    public function test_it_merges_query_language_and_zone(): void
    {
        $formRequest = $this->makeFormRequest([
            'broker_id' => '1',
            'entity_type' => 'account-type',
            'entity_id' => '1',
        ], [
            'language_code' => 'fr',
            'zone_code' => 'eu',
        ]);

        $formRequest->validateResolved();

        $this->assertSame('fr', $formRequest->input('language_code'));
        $this->assertSame('eu', $formRequest->input('zone_code'));
    }
}
