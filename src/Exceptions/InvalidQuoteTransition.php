<?php

namespace Mimisk\LaravelQuotes\Exceptions;

use RuntimeException;

final class InvalidQuoteTransition extends RuntimeException
{
    public static function onlyDraftQuotesCanBeSent(): self
    {
        return new self('Only draft quotes can be sent.');
    }

    public static function onlyDraftQuotesCanBeUpdated(): self
    {
        return new self('Only draft quotes can be updated.');
    }

    public static function onlySentQuotesCanBeAccepted(): self
    {
        return new self('Only sent quotes can be accepted.');
    }

    public static function onlySentQuotesCanBeRejected(): self
    {
        return new self('Only sent quotes can be rejected.');
    }

    public static function onlySentQuotesCanBeExpired(): self
    {
        return new self('Only sent quotes can be expired.');
    }

    public static function onlyDraftOrRejectedQuotesCanBeDeleted(): self
    {
        return new self('Only draft or rejected quotes can be deleted.');
    }

    public static function alreadySent(): self
    {
        return new self('This quote has already been sent.');
    }

    public static function alreadyAccepted(): self
    {
        return new self('This quote has already been accepted.');
    }

    public static function alreadyRejected(): self
    {
        return new self('This quote has already been rejected.');
    }

    public static function alreadyExpired(): self
    {
        return new self('This quote has already expired.');
    }
}
