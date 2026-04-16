<?php
namespace Modules\Brokers\Enums;

enum UrlTypeEnum: string
{
    case MOBILE = 'mobile';
    case WEBPLATFORM = 'webplatform';
    case SWAP = 'swap';
    case COMMISSION = 'commission';
    CASE IB_AFFILIATE_LINK = 'sign-up-ib-affiliate-link';
    CASE SUB_IB_AFFILIATE_LINK = 'sign-up-sub-ib-affiliate-link';
}