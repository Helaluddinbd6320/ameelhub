<?php

namespace App\Exceptions;

use RuntimeException;

class WalletException extends RuntimeException
{
    public static function insufficientBalance(float $required, float $available): self
    {
        return new self(
            "অপর্যাপ্ত ব্যালেন্স। প্রয়োজন: {$required} SAR, আছে: {$available} SAR"
        );
    }

    public static function insufficientHeld(float $required, float $held): self
    {
        return new self(
            "অপর্যাপ্ত held balance। প্রয়োজন: {$required} SAR, আছে: {$held} SAR"
        );
    }

    public static function negativeAmount(): self
    {
        return new self('Amount অবশ্যই শূন্যের বেশি হতে হবে।');
    }

    public static function activeDisputeBlock(): self
    {
        return new self('সক্রিয় বিরোধ থাকায় withdrawal সম্ভব নয়।');
    }

    public static function belowMinimumWithdrawal(float $min): self
    {
        return new self("সর্বনিম্ন withdrawal পরিমাণ {$min} SAR।");
    }

    public static function dailyWithdrawalLimitExceeded(int $limit): self
    {
        return new self("আজকের জন্য সর্বোচ্চ {$limit}টি withdrawal request সীমা শেষ হয়ে গেছে।");
    }

    public static function invalidWithdrawalStatus(): self
    {
        return new self('এই withdrawal request ইতিমধ্যে প্রসেস করা হয়েছে।');
    }
}