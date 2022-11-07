<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use EasyRdf\Http\Exception;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the tembang categories.
     * 
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $result = $this->sparql->query("
                SELECT ?category ?name ?alias ?description WHERE {
                    ?category rdfs:subClassOf tb:TembangBali ;
                        rdfs:label ?name ;
                        tb:alias ?alias ;
                        tb:description ?description .
                    FILTER(lang(?name) = 'id')
                    FILTER(lang(?description) = 'id')
                } ORDER BY ?category
            ");

            $categories = [];

            if ($result->numRows() > 0) {
                foreach ($result as $item) {
                    $category = [
                        'id' => $this->parseData($item->category, true),
                        'name' => $this->parseData($item->name, true),
                        'alias' => $this->parseData($item->alias, true),
                        'description' => $this->parseData($item->description, true),
                    ];

                    array_push($categories, $category);
                }
            }

            return ResponseFormatter::success($categories);
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    /**
     * Display a listing of the tembang sub-categories.
     * Only: Sekar Rare & Sekar Alit.
     * 
     * @return \Illuminate\Http\Response
     */
    public function subCategoryIndex(Request $request)
    {
        if ($request->query('name') == 'SekarAlit' || $request->query('name') == 'SekarMadya' || $request->query('name') == 'SekarRare') {
            try {
                $result = $this->sparql->query("
                    SELECT ?subCategory ?name ?description WHERE {
                        ?subCategory rdfs:subClassOf tb:" . $request->query('name') . " ;
                            rdfs:label ?name ;
                            tb:description ?description .
                    }
                ");
    
                $subCategories = [];
    
                if ($result->numRows() > 0) {
                    foreach ($result as $item) {
                        $category = [
                            'id' => $this->parseData($item->subCategory, true),
                            'name' => $this->parseData($item->name, true),
                            'description' => $this->parseData($item->description, true),
                        ];
    
                        array_push($subCategories, $category);
                    }
                }
    
                return ResponseFormatter::success($subCategories);
            } catch (Exception $e) {
                return ResponseFormatter::error($e->getMessage());
            }
        } else {
            return ResponseFormatter::error("Request query not found.", 404);
        }
    }
}
