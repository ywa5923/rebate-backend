<?php

declare(strict_types=1);

namespace Modules\Brokers\DTOs; // sau unde țineți DTO-urile în modul

use Modules\Brokers\Transformers\AffiliateLinkCollection;

final readonly class IbAffiliateLinksDTO
{
    public function __construct(
        public AffiliateLinkCollection $ibAffiliateUrls,
        public AffiliateLinkCollection $subIbAffiliateUrls,
    ) {
    }
}
