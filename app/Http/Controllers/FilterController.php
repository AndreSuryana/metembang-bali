<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use EasyRdf\Http\Exception;
use App\Helpers\ResponseFormatter;

class FilterController extends Controller
{
    /**
     * Display a listing of the rules.
     *
     * @return \Illuminate\Http\Response
     */
    public function rules()
    {
        try {
            $result = $this->sparql->query("
                SELECT DISTINCT ?rule ?guruDingdong ?guruWilang ?guruGatra WHERE {
                    ?rule a tb:Rule ;
                        tb:hasGuruDingdong ?guruDingdong ;
                        tb:hasGuruWilang ?guruWilang ;
                        tb:hasGuruGatra ?guruGatra .
                } ORDER BY ?rule
            ");

            $rules = [];

            if ($result->numRows() > 0) {
                foreach ($result as $item) {
                    $rule = [
                        'id' => $this->parseData($item->rule, true),
                        'guru_dingdong' => $this->parseData($item->guruDingdong, true),
                        'guru_wilang' => $this->parseData($item->guruWilang, true),
                        'guru_gatra' => $this->parseData($item->guruGatra, true),
                    ];

                    array_push($rules, $rule);
                }
            }

            return ResponseFormatter::success($rules);
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    /**
     * Display a listing of the usages types.
     *
     * @return \Illuminate\Http\Response
     */
    public function usageTypes()
    {
        try {
            $result = $this->sparql->query("
                SELECT DISTINCT ?type ?name ?description WHERE { 
                    ?type rdfs:subClassOf tb:Usage .
                    ?type rdfs:label ?name ;
                        tb:description ?description .
                    FILTER(lang(?name) = 'id')
  	            FILTER(lang(?description) = 'id')
                } ORDER BY ?name
            ");

            $types = [];

            if ($result->numRows() > 0) {
                foreach ($result as $item) {
                    $type = [
                        'id' => $this->parseData($item->type, true),
                        'name' => $this->parseData($item->name),
                        'description' => $this->parseData($item->description)
                    ];

                    array_push($types, $type);
                }
            }

            return ResponseFormatter::success($types);
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    /**
     * Display a listing of the usage individuals.
     *
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function usages(Request $request)
    {
        if ($this->isRequestQueryUsageValid($request->query('type'))) {
            try {
                $result = $this->sparql->query(
                    $this->getUsagesQuery($request->query('type'))
                );

                $usages = [];

                if ($result->numRows() > 0) {
                    foreach ($result as $item) {
                        $usage = [
                            'id' => $this->parseData($item->usage, true),
                            'type_id' => $request->query('type') ? $request->query('type')
                                : $this->parseData($item->usageType, true),
                            'activity' => $this->parseData($item->activity)
                        ];

                        array_push($usages, $usage);
                    }

                    return ResponseFormatter::success($usages);
                } else {
                    return ResponseFormatter::success($usages, 'Empty result set.');
                }
            } catch (Exception $e) {
                return ResponseFormatter::error($e->getMessage());
            }
        } else {
            return ResponseFormatter::error('Request query not found.', 404);
        }
    }

    /**
     * Display a listing of the mood individuals.
     *
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function moods()
    {
        try {
            $result = $this->sparql->query("
                SELECT DISTINCT ?mood ?moodDescription WHERE {
                    ?mood a tb:Mood ;
                    tb:hasDescription ?moodDescription .
                } ORDER BY ?mood
            ");

            $moods = [];

            if ($result->numRows() > 0) {
                foreach ($result as $item) {
                    $mood = [
                        'id' => $this->parseData($item->mood, true),
                        'description' => $this->parseData($item->moodDescription)
                    ];

                    array_push($moods, $mood);
                }
            }

            return ResponseFormatter::success($moods);
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    /**
     * Get usages query.
     * 
     * @param string
     * @return string
     */
    private function getUsagesQuery(String $requestQuery = null)
    {
        if ($requestQuery == null) {
            return "SELECT * WHERE {
                ?usageType rdfs:subClassOf tb:Usage .
                ?usage a ?usageType ;
                       tb:hasActivity ?activity .
              } ORDER BY ?usage";
        } else {
            return "SELECT * WHERE {
                ?usage a tb:$requestQuery ;
                       tb:hasActivity ?activity .
              } ORDER BY ?usage";
        }
    }

    /**
     * Validate request query usages.
     * 
     * @param string
     * @return boolean
     */
    private function isRequestQueryUsageValid(String $requestQuery = null)
    {
        if ($requestQuery == null) return true;

        $result = $this->sparql->query("
            SELECT * WHERE {
                ?usageType rdfs:subClassOf tb:Usage .
            }
        ");

        foreach ($result as $item) {
            if ($requestQuery === $this->parseData($item->usageType, true)) {
                return true;
            }
        }

        return false;
    }
}
