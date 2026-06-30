<?php

namespace Tests\Integration;

use App\Services\BotAIService;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

class BotAIServiceParseJobTest extends TestCase
{
    #[Group('ai')]
    public function test_parse_job_matches_fixtures(): void
    {
        if (! filled(env('DEEPSEEK_API_KEY'))) {
            $this->markTestSkipped('Set DEEPSEEK_API_KEY in .env to run this test.');
        }

        $directory = base_path('tests/Fixtures/BotAIService/parse-job');
        $files = glob($directory.'/*.json') ?: [];

        sort($files);

        $fixtureCount = count($files);

        $this->assertNotEmpty($files, 'No JSON fixtures found in tests/Fixtures/BotAIService/parse-job');

        fwrite(STDERR, sprintf(
            "\nProcessing %d JSON fixture(s)...\n",
            $fixtureCount,
        ));

        $totalFieldsVerified = 0;

        foreach ($files as $path) {
            $name = basename($path);
            $fixture = json_decode(file_get_contents($path), true);
            $fieldCount = count($fixture['expected']);

            fwrite(STDERR, sprintf("  → %s (%d fields)\n", $name, $fieldCount));

            $actual = app(BotAIService::class)->parseJob([
                'rawText' => $fixture['rawText'],
            ]);

            $verified = 0;

            foreach ($fixture['expected'] as $field => $expected) {
                $this->assertArrayHasKey($field, $actual, "{$name}: missing field {$field}");
                $this->assertExpectedField($name, $field, $expected, $actual[$field]);
                $verified++;
            }

            $totalFieldsVerified += $verified;

            fwrite(STDERR, sprintf(
                "  ✓ %s — %d/%d fields verified\n",
                $name,
                $verified,
                $fieldCount,
            ));
        }

        fwrite(STDERR, sprintf(
            "\nDone — %d JSON fixture(s), %d fields verified\n",
            $fixtureCount,
            $totalFieldsVerified,
        ));
    }

    private function assertExpectedField(string $fixture, string $field, mixed $expected, mixed $actual): void
    {
        if (is_array($expected) && array_key_exists('requiredDate', $expected)) {
            $this->assertNotNull(
                $actual,
                "{$fixture}: field {$field} must be present and not null",
            );
            $this->assertIsString(
                $actual,
                "{$fixture}: field {$field} must be a mysql datetime string",
            );
            $parsed = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $actual);
            $this->assertNotFalse(
                $parsed,
                "{$fixture}: field {$field} must match Y-m-d H:i:s format, got: ".json_encode($actual),
            );

            return;
        }

        if (is_array($expected) && array_key_exists('oneOf', $expected)) {
            $this->assertContains(
                $actual,
                $expected['oneOf'],
                "{$fixture}: field {$field} got ".json_encode($actual).', expected one of: '.json_encode($expected['oneOf']),
            );

            return;
        }

        if (is_array($expected) && array_is_list($expected)) {
            $this->assertEquals(
                $expected,
                $actual,
                "{$fixture}: field {$field} mismatch. Expected: ".json_encode($expected).'. Got: '.json_encode($actual),
            );

            return;
        }

        $this->assertEquals(
            $expected,
            $actual,
            "{$fixture}: field {$field} mismatch. Expected: ".json_encode($expected).'. Got: '.json_encode($actual),
        );
    }
}
