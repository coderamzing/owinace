<?php

namespace App\Jobs;

use App\Models\Portfolio;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessPortfolioCsvImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(
        public string $storagePath,
        public int $teamId,
        public int $userId,
    ) {}

    public function handle(): void
    {
        $filePath = Storage::disk('local')->path($this->storagePath);
        $handle = fopen($filePath, 'r');

        if ($handle === false) {
            throw new \RuntimeException('Unable to open import file.');
        }

        $header = null;
        $rowNumber = 0;
        $queuedCount = 0;
        $skippedCount = 0;

        try {
            while (($row = fgetcsv($handle, 0)) !== false) {
                $rowNumber++;

                if (! $header) {
                    $header = $this->normalizeHeader($row);
                    $this->validateHeader($header);

                    continue;
                }

                if (empty(array_filter($row))) {
                    continue;
                }

                if (count($row) < count($header)) {
                    $row = array_pad($row, count($header), '');
                }

                $data = array_combine($header, array_slice($row, 0, count($header)));

                if (! $data) {
                    $skippedCount++;
                    Log::warning('Portfolio import skipped invalid row', ['row' => $rowNumber]);

                    continue;
                }

                $portfolioData = $this->buildPortfolioData($data);

                if ($portfolioData === null) {
                    $skippedCount++;
                    Log::warning('Portfolio import skipped invalid row', [
                        'row' => $rowNumber,
                        'reason' => 'Missing required fields, description exceeds '.Portfolio::DESCRIPTION_MAX_WORDS.' words, or invalid scale (must be 1-10)',
                    ]);

                    continue;
                }

                CreateImportedPortfolio::dispatch($portfolioData, $rowNumber);
                $queuedCount++;
            }
        } finally {
            fclose($handle);
            Storage::disk('local')->delete($this->storagePath);
        }

        Log::info('Portfolio CSV import queued', [
            'team_id' => $this->teamId,
            'user_id' => $this->userId,
            'queued' => $queuedCount,
            'skipped' => $skippedCount,
        ]);
    }

    protected function normalizeHeader(array $row): array
    {
        return array_map(
            fn (string $column): string => strtolower(trim($column)),
            $row,
        );
    }

    protected function validateHeader(array $header): void
    {
        $requiredHeaders = ['title', 'description', 'keywords', 'url'];
        $missingHeaders = array_diff($requiredHeaders, $header);

        if (! empty($missingHeaders)) {
            throw new \InvalidArgumentException(
                'CSV is missing required headers: '.implode(', ', $missingHeaders),
            );
        }
    }

    protected function buildPortfolioData(array $data): ?array
    {
        $title = trim((string) ($data['title'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));
        $keywordsRaw = trim((string) ($data['keywords'] ?? ''));
        $url = trim((string) ($data['url'] ?? ''));

        if ($title === '' || $description === '' || $keywordsRaw === '' || $url === '') {
            return null;
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        if (Portfolio::exceedsDescriptionWordLimit($description)) {
            return null;
        }

        $keywords = array_values(array_filter(array_map(
            'trim',
            explode('|', $keywordsRaw),
        )));

        if (empty($keywords)) {
            return null;
        }

        if (count($keywords) > 15) {
            throw new \InvalidArgumentException('Keywords must not exceed 15 items.');
        }

        $scale = $this->parseScale($data['scale'] ?? '');

        if ($scale === false) {
            return null;
        }

        return [
            'team_id' => $this->teamId,
            'created_by_id' => $this->userId,
            'title' => $title,
            'url' => $url,
            'description' => $description,
            'keywords' => $keywords,
            'is_active' => $this->parseBoolean($data['is_active'] ?? true),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'scale' => $scale,
        ];
    }

    protected function parseScale(mixed $value): string|false
    {
        $normalized = trim((string) $value);

        if ($normalized === '') {
            return '';
        }

        if (! ctype_digit($normalized)) {
            return false;
        }

        $scale = (int) $normalized;

        if ($scale < 1 || $scale > 10) {
            return false;
        }

        return (string) $scale;
    }

    protected function parseBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower(trim((string) $value));

        if ($normalized === '') {
            return true;
        }

        return in_array($normalized, ['1', 'true', 'yes', 'y', 'on'], true);
    }
}
