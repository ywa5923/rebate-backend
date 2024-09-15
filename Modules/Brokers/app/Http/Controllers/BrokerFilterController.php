<?php

namespace Modules\Brokers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Brokers\Repositories\CompanyRepository;
use Modules\Brokers\Repositories\CompanyUniqueListInterface;
use Modules\Brokers\Repositories\RegulatorRepository;
use Modules\Brokers\Services\BrokerFilterQueryParser;

class BrokerFilterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(BrokerFilterQueryParser $queryParser,Request $request)
    {
       $queryParser->parse($request);
       $language=$queryParser->getWhereParam("language");
        
        
       $companyRepo= new CompanyRepository();
       $regulatorRepo=new RegulatorRepository();
       $officesList= $companyRepo->getUniqueList($language,CompanyUniqueListInterface::OFFICES);
      
       $headquartersList= $companyRepo->getUniqueList($language,CompanyUniqueListInterface::HEADQUARTERS);
       $regulatorsList=$regulatorRepo->getUniqueList($language);

       return  [[
        "field"=>"offices",
         "type"=>"checkbox",
         "options"=>$this->transform($officesList)
       ],
       [
        "field"=>"headquarters",
         "type"=>"checkbox",
         "options"=>$this->transform($headquartersList)
       ],
       [
        "field"=>"regulators",
         "type"=>"checkbox",
         "options"=>$this->transform($regulatorsList)
       ]
       ];
      
    }

    public function transform(array $data)
    {
        $result=[];
        foreach ($data as $key=>$value)
        {
           $result[]=["name"=>$value,"value"=>$key];
        }
        return $result;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('brokers::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('brokers::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('brokers::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
