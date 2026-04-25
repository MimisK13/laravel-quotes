<?php

namespace Mimisk\LaravelQuotes\Commands;

use Illuminate\Console\Command;
use Mimisk\LaravelQuotes\Actions\ExpireQuoteAction;
use Mimisk\LaravelQuotes\Enums\QuoteStatus;

final class ExpireQuotesCommand extends Command
{
    protected $signature = 'quotes:expire';

    protected $description = 'Expire sent quotes past their valid until date.';

    public function handle(ExpireQuoteAction $expireQuoteAction): int
    {
        $quoteModel = config('quotes.models.quote');

        $quoteModel::query()
            ->where('status', QuoteStatus::SENT)
            ->whereNotNull('valid_until')
            ->where('valid_until', '<', now())
            ->each(fn ($quote) => $expireQuoteAction->handle($quote));

        $this->info('Expired quotes processed.');

        return self::SUCCESS;
    }
}
