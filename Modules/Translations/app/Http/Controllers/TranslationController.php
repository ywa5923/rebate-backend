<?php

namespace Modules\Translations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Brokers\Models\Broker;
use Modules\Brokers\Models\BrokerOption;
use Modules\Translations\Models\Translation;
use Modules\Translations\Services\TranslationService;

class TranslationController extends Controller
{

    public function __construct(private TranslationService $translator)
    {
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $language = "ro";

        $model = ucfirst($request->query("model"));
        $properties = $request->query("properties");

        $dynamicProperties = $request->query("dynamicProperties");
        $fullClass = "Modules\Brokers\Models\\" . $model;


        if ($properties == 'all') {

            return $this->translator->translateTableColumns($fullClass, $language);

        } else if ($dynamicProperties) {

            return $this->translator->translatePropertyArray(BrokerOption::class, $language, explode(",", $dynamicProperties));
            
        }

        //{{PATH}}/translations?model=broker&dynamicProperties=promotion_details,short_payment_options,commission_value
        //{{PATH}}/translations?model=broker&properties=all
        //{{PATH}}/translations?model=broker&properties=all

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('translations::create');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/translation",
     *     tags={"Translation"},
     *     summary="Add translation data",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="translationable_type", type="string"),
     *             @OA\Property(property="translationable_id", type="string"),
     *             @OA\Property(property="language_code", type="string"),
     *             @OA\Property(property="metadata", type="json"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Successful operation"
     *     ),
     * 
     *     @OA\Response(
     *         response=422,
     *         description="These credentials do not match our records"
     *     )
     * )
     */
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * @OA\Get(
     *     path="/api/v1/translation/{id}",
     *     tags={"Translation"},
     *     summary="Translate service API",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     ),
     * 
     *     @OA\Response(
     *         response=422,
     *         description="These request do not match our records"
     *     )
     * )
     */
    public function show($id)
    {
        return view('translations::show');
    }



    /**
     * @OA\Put(
     *     path="/api/v1/translation/{id}",
     *     tags={"Translation"},
     *     summary="Add translation data",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="translationable_type", type="string"),
     *             @OA\Property(property="translationable_id", type="string"),
     *             @OA\Property(property="language_code", type="string"),
     *             @OA\Property(property="metadata", type="json"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Successful operation"
     *     ),
     * 
     *     @OA\Response(
     *         response=422,
     *         description="These credentials do not match our records"
     *     )
     * )
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/transaltion/{id}",
     *     tags={"Translation"},
     *     summary="Delete translation",
     *     @OA\Response(
     *         response=204,
     *         description="Successful operation"
     *     ),
     * 
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function destroy($id)
    {
        //
    }
}
