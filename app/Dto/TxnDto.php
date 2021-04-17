<?php
namespace App\Dto;

use Carbon\Carbon;
use Spatie\DataTransferObject\DataTransferObject;

class TxnDto extends DataTransferObject
{
    public ?string $txHash;
    public ?string $status;
    public ?string $blockHeight;
    public ?Carbon $timestamp;
    public ?string $type;
    public ?string $fee;
    public ?string $feeSymbol;
    public ?string $asset;
    public ?string $memo;
    public ?string $fromAddress;
    public ?string $toAddress;
    public ?string $value;
    public ?string $valueSymbol;
}
