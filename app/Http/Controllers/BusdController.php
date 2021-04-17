<?php

declare(strict_types=1);
namespace App\Http\Controllers;

use App\Dto\TxnDto;
use Carbon\Carbon;
use PHPHtmlParser\Dom;
use PHPHtmlParser\Dom\Node\HtmlNode;

class BusdController extends Controller
{
    /**
     * @param string $hash
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \PHPHtmlParser\Exceptions\ChildNotFoundException
     * @throws \PHPHtmlParser\Exceptions\CircularException
     * @throws \PHPHtmlParser\Exceptions\ContentLengthException
     * @throws \PHPHtmlParser\Exceptions\LogicalException
     * @throws \PHPHtmlParser\Exceptions\NotLoadedException
     * @throws \PHPHtmlParser\Exceptions\StrictException
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function txn(string $hash)
    {
        $url = "https://explorer.binance.org/tx/$hash";

        $dom = new Dom();
        $dom->loadFromUrl($url);

        $rows = $dom->find("div[class^='DetailCard__Row']");

        $getNodeText = function (HtmlNode $node) {
            $rowData = $node->getChildren()[1];

            $text = $rowData->firstChild()->text ?:
                $rowData->firstChild()->firstChild()->text ?:
                $rowData->firstChild()->firstChild()->firstChild()->text ?: '';

            return trim($text);
        };

        $txnDto = new TxnDto;

        /** @var HtmlNode $row */
        foreach ($rows as $row) {
            if (!empty($row->getChildren())) {
                /** @var HtmlNode $firstChildren */
                $firstChildren    = $row->firstChild();
                $firstChildrenStr = (string) $firstChildren;

                switch (true) {
                    case strstr($firstChildrenStr, 'TxHash:'):
                        $txnDto->txHash = $getNodeText($row);
                        break;

                    case strstr($firstChildrenStr, 'TxReceipt Status'):
                        $txnDto->status = $getNodeText($row);
                        break;

                    case strstr($firstChildrenStr, 'Block Height:'):
                        $txnDto->blockHeight = $getNodeText($row);
                        break;

                    case strstr($firstChildrenStr, 'TimeStamp:'):
                        $text      = $getNodeText($row);
                        $arr       = explode('[', $text);
                        $timestamp = substr($arr[1], 0, -1);

                        $txnDto->timestamp = new Carbon($timestamp);
                        break;

                    case strstr($firstChildrenStr, 'Transaction Type:'):
                        $txnDto->type = $getNodeText($row);
                        break;

                    case strstr($firstChildrenStr, 'Fee:'):
                        $text = $getNodeText($row);
                        $textArr = explode(' ', $text);

                        $txnDto->fee       = $textArr[0];
                        $txnDto->feeSymbol = $textArr[1];
                        break;

                    case strstr($firstChildrenStr, 'Asset:'):
                        $txnDto->asset = $getNodeText($row);
                        break;

                    case strstr($firstChildrenStr, 'Memo:'):
                        $txnDto->memo = $getNodeText($row);
                        break;

                    case strstr($firstChildrenStr, 'From:'):
                        $txnDto->fromAddress = $getNodeText($row);
                        break;

                    case strstr($firstChildrenStr, 'To:'):
                        $txnDto->toAddress = $getNodeText($row);
                        break;

                    case strstr($firstChildrenStr, 'Value:'):
                        $text = $getNodeText($row);
                        $textArr = explode(' ', $text);

                        $txnDto->value       = $textArr[0];
                        $txnDto->valueSymbol = $textArr[1];
                        break;

                    default:
                        break;
                }


            }
        }

        return response()->json($txnDto);
    }
}