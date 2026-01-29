<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Brokers\Models\Url;
use Modules\Brokers\Transformers\URLResource;
use Modules\Brokers\Models\AccountType;
use App\Utilities\ModelHelper;
use Modules\Brokers\Services\UrlService;

class UrlController extends Controller
{
    protected UrlService $urlService;

    public function __construct(UrlService $urlService)
    {
        $this->urlService = $urlService;
    }
   

    public function getGroupedUrls($broker_id, $entity_type, $entity_id, Request $request)
    {
        //http://localhost:8080/api/v1/urls/2/account-type/1?zone_code=eu&language_code=en
        //go get urls for all account types $entity_id is "all"
        //ex for all http://localhost:8080/api/v1/urls/2/account-type/all?zone_code=eu&language_code=en

        try{
            $this->urlService->validateData($broker_id, $entity_type, $entity_id, $request);
            $zone_code = $request->query('zone_code') ?? null;
            $language_code = $request->query('language_code') ?? 'en';
       
            $isAdmin = app('isAdmin');
            
    
          $urls = $this->urlService->getUrlsByEntity($broker_id, $entity_type, $entity_id, $zone_code, $language_code);
          $transformed = URLResource::collection($urls);

         $grouped = $transformed->groupBy([
            function ($item) {
                return $item['urlable_id'] ? $item['urlable_id'] : "master-links";
            },
            function ($item) {
                return $item['url_type'];
            }
        ]);
        $masterLinks = $grouped['master-links'] ?? [];
        unset($grouped['master-links']);

        return response()->json([
            'success' => true,
            'data'=>[
                'links_grouped_by_account_id' =>  $grouped,
                'master_links_grouped_by_type' => $masterLinks,
                'links_groups' => ['mobile', 'webplatform', 'swap', 'commission']
            ]

        ]);
        }catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }




    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
