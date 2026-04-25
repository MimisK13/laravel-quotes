<?php

namespace Mimisk\LaravelQuotes\Actions;

use Illuminate\Support\Facades\DB;
use Mimisk\LaravelQuotes\Enums\QuoteStatus;
use Mimisk\LaravelQuotes\Exceptions\InvalidQuoteTransition;
use Mimisk\LaravelQuotes\Models\Quote;

final class DeleteQuoteAction
{
    public function handle(Quote $quote): void
    {
        DB::transaction(function () use ($quote): void {

            if (! in_array($quote->status, [
                QuoteStatus::DRAFT,
                QuoteStatus::REJECTED,
            ])) {
                throw InvalidQuoteTransition::onlyDraftOrRejectedQuotesCanBeDeleted();
            }

            $quote->delete();
        });
    }
}

