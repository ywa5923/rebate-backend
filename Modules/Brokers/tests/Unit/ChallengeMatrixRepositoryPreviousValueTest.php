<?php

namespace Modules\Brokers\Tests\Unit;

use Modules\Brokers\Models\ChallengeMatrixValue;
use Modules\Brokers\Repositories\ChallengeMatrixRepository;
use Modules\Translations\Repositories\TranslationRepository;
use PHPUnit\Framework\TestCase;

class ChallengeMatrixRepositoryPreviousValueTest extends TestCase
{
    private ChallengeMatrixRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new ChallengeMatrixRepository(new TranslationRepository(), new ChallengeMatrixValue());
    }

    public function test_chains_current_value_in_front_of_existing_history(): void
    {
        $result = $this->repository->buildPreviousValueHistory('{"text": "7"}', '{"text": "5->3"}');

        $this->assertSame('{"text":"7->5->3"}', $result);
    }

    public function test_starts_history_when_no_previous_value_exists(): void
    {
        $result = $this->repository->buildPreviousValueHistory('{"text": "7"}', null);

        $this->assertSame('{"text":"7"}', $result);
    }

    public function test_keeps_history_when_current_value_text_is_null(): void
    {
        $result = $this->repository->buildPreviousValueHistory('{"text": null}', '{"text": "5"}');

        $this->assertSame('{"text":"5"}', $result);
    }

    public function test_returns_null_when_both_values_are_empty(): void
    {
        $this->assertNull($this->repository->buildPreviousValueHistory(null, null));
        $this->assertNull($this->repository->buildPreviousValueHistory('{"text": null}', '{"text": ""}'));
    }

    public function test_handles_invalid_json_gracefully(): void
    {
        $result = $this->repository->buildPreviousValueHistory('{"text": "7"}->', '"just a string"');

        $this->assertNull($result);
    }

    public function test_result_is_always_valid_json(): void
    {
        $result = $this->repository->buildPreviousValueHistory('{"text": "7"}', '{"text": "5"}');

        $this->assertNotNull(json_decode($result, true));
    }
}
