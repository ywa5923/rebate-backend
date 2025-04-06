<?php

namespace Modules\Brokers\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;
use App\Utilities\TranslateTrait;

class BrokerResource extends JsonResource
{

    use TranslateTrait;
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array|null
    {
       
      // return $this->getBrokerWithRelations();
       $dynamic_columns = $this->additional['dynamic_columns'] ?? null;
       if(empty($dynamic_columns))
       {
        return $this->getBrokerWithRelations();
       }
   
       $dynamicOptionsValues=DynamicOptionValueResource::collection ($this->whenLoaded('dynamicOptionsValues'));
       
       if($dynamicOptionsValues->resource instanceof MissingValue){
           return null;
       }
       $dynamicOptionsValuesResolved= $dynamicOptionsValues->resolve();
       // $dynamicOptionsValues=DynamicOptionValueResource::collection ($this->whenLoaded('dynamicOptionsValues'))->resolve();
      
       // $obj=["id"=>$this->id];
        $obj=[];
        $home_url="";
        $logo="";
       foreach($dynamic_columns as $column){

          if($column=="regulators"){
            $obj[$column]=$this->getRegulatorString();
            continue;
          }

         
          $dynamicOptions=array_filter($dynamicOptionsValuesResolved,fn($option) =>$option['option_slug']===$column);
          //concatenate dynamic options if there are more with same slug
          $dynamicOptionValue="";
          foreach($dynamicOptions as $dynOpt){
            //to do :concatenate for options with unit and urls
         
            $metadata=(isset($dynOpt["metadata"]))?(current(json_decode($dynOpt["metadata"],true))):"";
            $dynamicOptionValue.=$dynOpt["value"]." ".$metadata."; ";
          }
         
          if($column=="home_url" || $column=="logo"){
             ${$column}=rtrim($dynamicOptionValue,"; ");
          }else{
            $obj[$column]=rtrim($dynamicOptionValue,"; ");
          }
        
       }
       $logoValue=$logo." ".$home_url;
       return ["logo"=>$logoValue]+$obj;
    }
    public function getBrokerWithRelations():array{
        return [
            "id"=>$this->id,
            //"logo" =>$this->whenNotNull($this->logo),
            // "favicon"=>$this->whenNotNull($this->favicon),
            // "trading_name"=>$this->translate("trading_name")."**##**".$this->home_url,
            // "home_url"=>$this->home_url,
            // "overall_rating"=>$this->overall_rating??0,
            // "user_rating"=>$this->user_rating,
            // "support_options"=>$this->translate("support_options"),
            // "account_type"=>$this->translate("account_type"),
            // "trading_instruments"=>$this->translate("trading_instruments"),
            // "account_currencies"=>$this->account_currencies,
            // "broker_type_id"=>$this->whenNotNull($this->broker_type_id),
            // "default_language"=>$this->whenNotNull($this->default_language),
          // "translations"=>TranslationResource::collection($this->whenLoaded('translations')),
          // "dynamic_options_values"=> $this->whenNotNull(DynamicOptionValueResource::collection ($this->whenLoaded('dynamicOptionsValues'))),
           "dynamic_options_values"=> DynamicOptionValueResource::collection ($this->whenLoaded('dynamicOptionsValues')),
           "companies"=>CompanyResource::collection($this->whenLoaded('companies')),
           "regulators"=>RegualtorResource::collection($this->whenLoaded('regulators'))
          ];
    }

    public function getRegulatorString():string|null
    {
        $regulators=RegualtorResource::collection($this->whenLoaded('regulators'));
       
        if($regulators->resource instanceof MissingValue){
            return null;
        }
        $regulatorsResolved=$regulators->resolve();
        $regulatorsString="";
        foreach($regulatorsResolved as $r){
            $regulatorsString.=$r["abreviation"]."-".$r["country"]."; ";
        }
       return rtrim($regulatorsString,"; ");
    }

   
}
