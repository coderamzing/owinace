<?php

namespace App\Services;

use App\Models\Portfolio;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PortfolioCsvExportService
{
    public const HEADERS = [
        'title',
        'description',
        'keywords',
        'url',
        'is_active',
        'sort_order',
        'scale',
    ];

    public function toStreamedResponse(?Builder $query = null): StreamedResponse
    {
        $filename = 'portfolios-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, self::HEADERS);

            $portfolios = ($query ?? Portfolio::query())
                ->orderBy('sort_order')
                ->orderBy('title')
                ->cursor();

            foreach ($portfolios as $portfolio) {
                fputcsv($handle, $this->formatRow($portfolio));
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * @return array<int, string>
     */
    protected function formatRow(Portfolio $portfolio): array
    {
        $keywords = is_array($portfolio->keywords)
            ? implode('|', $portfolio->keywords)
            : (string) $portfolio->keywords;

        return [
            $portfolio->title,
            $portfolio->description,
            $keywords,
            $portfolio->url ?? '',
            $portfolio->is_active ? '1' : '0',
            (string) ($portfolio->sort_order ?? 0),
            (string) ($portfolio->scale ?? ''),
        ];
    }
}
