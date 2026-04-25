<?php

namespace Mimisk\LaravelQuotes\Services;

use Mimisk\LaravelQuotes\Actions\AcceptQuoteAction;
use Mimisk\LaravelQuotes\Actions\CreateQuoteAction;
use Mimisk\LaravelQuotes\Actions\DeleteQuoteAction;
use Mimisk\LaravelQuotes\Actions\ExpireQuoteAction;
use Mimisk\LaravelQuotes\Actions\RejectQuoteAction;
use Mimisk\LaravelQuotes\Actions\SendQuoteAction;
use Mimisk\LaravelQuotes\Actions\UpdateQuoteAction;
use Mimisk\LaravelQuotes\DTOs\QuoteData;
use Mimisk\LaravelQuotes\Models\Quote;

final class QuotesService
{
    public function create(QuoteData $data): Quote
    {
        return app(CreateQuoteAction::class)->handle($data);
    }

    public function update(Quote $quote, QuoteData $data): Quote
    {
        return app(UpdateQuoteAction::class)->handle($quote, $data);
    }

    public function send(Quote $quote): Quote
    {
        return app(SendQuoteAction::class)->handle($quote);
    }

    public function accept(Quote $quote): Quote
    {
        return app(AcceptQuoteAction::class)->handle($quote);
    }

    public function reject(Quote $quote): Quote
    {
        return app(RejectQuoteAction::class)->handle($quote);
    }

    public function expire(Quote $quote): Quote
    {
        return app(ExpireQuoteAction::class)->handle($quote);
    }

    public function delete(Quote $quote): void
    {
        app(DeleteQuoteAction::class)->handle($quote);
    }
}
