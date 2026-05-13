<?php
namespace Modules\Brokers\Enums;

enum UrlTypeEnum: string
{
    case MOBILE = 'mobile';
    case TRADING_PLATFORM = 'trading-platform';
    case SPREAD_TYPE = 'spread-type';
    case SWAP = 'swap';
    case COMMISSION = 'commission';
    CASE IB_AFFILIATE_LINK = 'sign-up-ib-affiliate-link';
    CASE SUB_IB_AFFILIATE_LINK = 'sign-up-sub-ib-affiliate-link';
}