<?php

namespace Modules\Brokers\Tests\Feature;

use Illuminate\Support\Facades\Validator;
use Modules\Brokers\Http\Requests\GetRegulatorsListRequest;
use Tests\TestCase;

class GetRegulatorsListRequestTest extends TestCase
{
    public function test_it_defaults_language_code_to_en_and_zone_id_to_null(): void
    {
        $request = GetRegulatorsListRequest::create('/', 'GET');
        $request->setContainer(app())->setRedirector(app('redirect'));
        $request->validateResolved();

        $this->assertSame('en', $request->input('language_code'));
        $this->assertNull($request->input('zone_id'));
    }

    public function test_it_accepts_valid_language_code_and_zone_id(): void
    {
        $rules = (new GetRegulatorsListRequest)->rules();

        $validator = Validator::make([
            'language_code' => 'ro',
            'zone_id' => 1,
        ], $rules);

        $this->assertFalse($validator->fails());
    }

    public function test_it_rejects_non_integer_zone_id(): void
    {
        $rules = (new GetRegulatorsListRequest)->rules();

        $validator = Validator::make([
            'language_code' => 'en',
            'zone_id' => 'invalid',
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('zone_id', $validator->errors()->toArray());
    }
}
