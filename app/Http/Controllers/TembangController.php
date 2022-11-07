<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\ViewHistory;
use EasyRdf\Http\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class TembangController extends Controller
{
    private $categories = ['SekarAgung', 'SekarAlit', 'SekarMadya', 'SekarRare'];

    /**
     * Display a listing of the tembang bali.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            // Get category query
            $categoryQuery = $this->getCategoryQuery($request->query('category'));
            $usageTypeQuery = $this->getUsageTypeQuery($request->query('usage_type'));
            $usageQuery = $this->getUsageQuery($request->query('usage'));
            $moodQuery = $this->getMoodQuery($request->query('mood'));
            $ruleQuery = $this->getRuleQuery($request->query('rule'));

            // Generate query
            $query = <<<EOT
                SELECT DISTINCT ?tembang ?title ?category ?subCategory ?coverPath WHERE {
                    $categoryQuery
                    ?tembang tb:hasTitle ?title ;
                        a ?category .
                    OPTIONAL { ?tembang tb:hasCoverPath ?coverPath }
                    $usageTypeQuery
                    $usageQuery
                    $moodQuery
                    $ruleQuery
                }
            EOT;
            
            // Execute query
            $result = $this->sparql->query($query);

            $list = [];

            if ($result->numRows() > 0) {
                foreach ($result as $item) {
                    $tembang = [
                        'id' => $this->parseData($item->tembang, true),
                        'title' => $this->parseData($item->title, true),
                        'category' => $this->parseData($item->category, true),
                        'sub_category' => property_exists($item, 'subCategory') ? $this->parseData($item->subCategory, true) : null,
                        'cover_url' => property_exists($item, 'coverPath') ? env('APP_URL') . $this->parseData($item->coverPath, true) : null,
                    ];

                    // If request query category is not part of $categories OR empty/null,
                    // then swap category and sub_category.
                    // Because the variable becomes inverted on query process.
                    if (!in_array($request->query('category'), $this->categories, true)) {
                        $category = $tembang['category'];
                        $tembang['category'] = $tembang['sub_category'];
                        $tembang['sub_category'] = $category;
                    }

                    if ($request->query('category') == null) {
                        $category = $tembang['category'];
                        $tembang['category'] = $tembang['sub_category'];
                        $tembang['sub_category'] = $category;
                    }

                    array_push($list, $tembang);
                }

                return ResponseFormatter::success([
                    'size' => count($list),
                    'list' => $list,
                    'query' => str_remove_blank_lines($query)
                ]);
            } else {
                return ResponseFormatter::success([
                    'size' => count($list),
                    'list' => $list,
                    'query' => str_remove_blank_lines($query)
                ], 'Empty result set.');
            }
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    /**
     * Show detail of the tembang bali.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $tembangUID)
    {
        try {
            $result = $this->sparql->query("
                SELECT DISTINCT * WHERE {
                    VALUES ?tembang { tb:$tembangUID }
                    ?tembang tb:hasTitle ?title ;
                        tb:hasLyrics ?lyrics ;
                        a ?category .
                    ?category rdfs:subClassOf tb:TembangBali .
                    FILTER(lang(?lyrics) = 'ban')
                    OPTIONAL { 
                        ?tembang tb:hasLyrics ?lyricsIDN .
                        FILTER(lang(?lyricsIDN) = 'id') .
                    }
                    OPTIONAL { ?tembang tb:hasAuthor ?author }
                    OPTIONAL { ?tembang tb:hasMeaning ?meaning  }
                    OPTIONAL { ?tembang tb:hasCoverPath ?coverPath }
                    OPTIONAL { ?tembang tb:hasCoverSource ?coverSource }
                    OPTIONAL { ?tembang tb:hasAudioPath ?audioPath }
                    OPTIONAL { ?tembang tb:hasDateAdded ?dateAdded }
                    OPTIONAL { 
                        ?tembang a ?subCategory .
                        ?subCategory rdfs:subClassOf ?category .
                    }
                    OPTIONAL { 
                        ?tembang tb:hasRule ?rule .
                        ?rule tb:hasGuruDingdong ?guruDingdong ;
                            tb:hasGuruWilang ?guruWilang ;
                            tb:hasGuruGatra ?guruGatra .
                    }
                    OPTIONAL { 
                        ?tembang tb:hasMood ?mood .
                        ?mood tb:hasDescription ?moodDescription .
                    }
                    OPTIONAL { 
                        ?tembang tb:hasUsage ?usage .
                        ?usage a ?usageType ;
                            tb:hasActivity ?activity .
                        FILTER(?usageType != owl:NamedIndividual)
                    }
                }
            ");

            $tembang = [];

            // Check result set is not empty
            if ($result->numRows() > 0) {
                // Main data
                $tembang['id'] = $this->parseData($result[0]->tembang, true);
                $tembang['title'] = $this->parseData($result[0]->title, true);
                $tembang['author'] = property_exists($result[0], 'author') ? $this->parseData($result[0]->author, true) : null;
                $tembang['category'] = $this->parseData($result[0]->category, true);
                $tembang['sub_category'] = property_exists($result[0], 'subCategory') ? $this->parseData($result[0]->subCategory, true) : null;
                $tembang['lyrics'] = explode('↵', $result[0]->lyrics);;
                $tembang['lyrics_idn'] = property_exists($result[0], 'lyricsIDN') ? explode('↵', $result[0]->lyricsIDN) : null;
                $tembang['meaning'] = property_exists($result[0], 'meaning') ? $this->parseData($result[0]->meaning, true) : null;
                $tembang['cover_url'] = property_exists($result[0], 'coverPath') ? env('APP_URL') . $this->parseData($result[0]->coverPath, true) : null;
                $tembang['audio_url'] = property_exists($result[0], 'audioPath') ? env('APP_URL') . $this->parseData($result[0]->audioPath, true) : null;
                $tembang['cover_source'] = property_exists($result[0], 'coverSource') ? $this->parseData($result[0]->coverSource, true) : null;
                $tembang['created_at'] = property_exists($result[0], 'dateAdded') ? $this->parseData($result[0]->dateAdded, true) : null;

                // Rule
                if (!empty($result[0]->rule)) {
                    $tembang['rule'] = [
                        'id' => $this->parseData($result[0]->rule, true),
                        'guru_dingdong' => $this->parseData($result[0]->guruDingdong, true),
                        'guru_wilang' => $this->parseData($result[0]->guruWilang, true),
                        'guru_gatra' => $this->parseData($result[0]->guruGatra, true)
                    ];
                } else {
                    $tembang['rule'] = null;
                }

                // Mood
                if (!empty($result[0]->mood)) {
                    $tembang['mood'] = [
                        'id' => $this->parseData($result[0]->mood, true),
                        'description' => $this->parseData($result[0]->moodDescription, true)
                    ];
                } else {
                    $tembang['mood'] = null;
                }

                // Usage
                if (!empty($result[0]->usage)) {
                    $usages = [];

                    foreach ($result as $item) {
                        $usage = [
                            'id' => $this->parseData($item->usage, true),
                            'type_id' => $this->parseData($item->usageType, true),
                            'activity' => $this->parseData($item->activity)
                        ];

                        array_push($usages, $usage);
                    }

                    $tembang['usages'] = $usages;
                } else {
                    $tembang['usages'] = null;
                }

                // If user is authenticated, then create new view history
                if (!empty(auth('sanctum')->user()->id)) {
                    ViewHistory::createNewViewHistory($request, $tembangUID);
                }

                return ResponseFormatter::success($tembang);
            } else {
                return ResponseFormatter::error('Data not found.', 404);
            }
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    /**
     * Display a listing the latest data tembang bali.
     * 
     * @return \Illuminate\Http\Response
     */
    public function latest()
    {
        try {
            $query = "
                SELECT DISTINCT ?tembang ?title ?category ?subCategory ?coverPath ?dateAdded WHERE {
                    ?tembang a tb:TembangBali ;
                        tb:hasTitle ?title ;
                        a ?category ;
                        tb:hasDateAdded ?dateAdded .
                    ?category rdfs:subClassOf tb:TembangBali .
                    OPTIONAL { 
                        ?tembang a ?subCategory .
                        ?subCategory rdfs:subClassOf ?category .
                        ?tembang tb:hasCoverPath ?coverPath .
                    }
                } ORDER BY DESC(?dateAdded) LIMIT 5
            ";

            $result = $this->sparql->query($query);

            $list = [];

            if ($result->numRows() > 0) {
                foreach ($result as $item) {
                    $data = [
                        'id' => $this->parseData($item->tembang, true),
                        'title' => $this->parseData($item->title, true),
                        'category' => $this->parseData($item->category, true),
                        'sub_category' => property_exists($item, 'subCategory') ? $this->parseData($item->subCategory, true) : null,
                        'cover_url' => property_exists($item, 'coverPath') ? env('APP_URL') . $this->parseData($item->coverPath, true) : null,
                        'date_added' => $this->parseData($item->dateAdded, true)
                    ];

                    array_push($list, $data);
                }
            }

            return ResponseFormatter::success([
                'size' => count($list),
                'list' => $list,
                'query' => str_remove_blank_lines($query)
            ]);
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    /**
     * Display a listing the top 5 most viewed data tembang bali.
     * 
     * @return \Illuminate\Http\Response
     */
    public function top()
    {
        try {
            $histories = DB::table('view_histories')
                ->select('tembang_uid', DB::raw('count(*) as view_count'))
                ->groupBy('tembang_uid')
                ->orderByDesc('view_count')
                ->limit(5)
                ->get();

            $list = [];

            foreach ($histories as $history) {
                $result = $this->sparql->query("
                    SELECT DISTINCT ?tembang ?title ?category ?subCategory ?coverPath ?dateAdded WHERE {
                        VALUES ?tembang { tb:" . $history->tembang_uid . " }
                        ?tembang a tb:TembangBali ;
                            tb:hasTitle ?title ;
                            a ?category ;
                            tb:hasDateAdded ?dateAdded .
                        ?category rdfs:subClassOf tb:TembangBali .
                        OPTIONAL { 
                            ?tembang a ?subCategory .
                            ?subCategory rdfs:subClassOf ?category .
                            ?tembang tb:hasCoverPath ?coverPath .
                        }
                    }
                ");

                $data = [
                    'id' => $this->parseData($result[0]->tembang, true),
                    'title' => $this->parseData($result[0]->title, true),
                    'category' => $this->parseData($result[0]->category, true),
                    'sub_category' => property_exists($result[0], 'subCategory') ? $this->parseData($result[0]->subCategory, true) : null,
                    'cover_url' => property_exists($result[0], 'coverPath') ? env('APP_URL') . $this->parseData($result[0]->coverPath, true) : null,
                    'view_count' => $history->view_count,
                    'date_added' => $this->parseData($result[0]->dateAdded, true)
                ];

                array_push($list, $data);
            }

            return ResponseFormatter::success([
                'size' => count($list),
                'list' => $list
            ]);
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    /**
     * Display a listing of the tembang bali.
     *
     * @return \Illuminate\Http\Response
     */
    public function random()
    {
        try {
            $result = $this->sparql->query("
                SELECT * WHERE {
                    ?tembang a tb:TembangBali .
                    BIND(RAND() as ?random) .
                } ORDER BY ?random LIMIT 1
            ");

            $tembangUID = null;

            if ($result->numRows() > 0) {
                $tembangUID = $this->parseData($result[0]->tembang, true);
            }

            return ResponseFormatter::success($tembangUID);
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    private function getCategoryQuery(String $category = null)
    {
        if ($category == null) {
            return <<<EOT
            ?tembang a ?category .
                    ?category rdfs:subClassOf tb:TembangBali .
                    OPTIONAL {
                        ?tembang a ?subCategory .
                        ?subCategory rdfs:subClassOf ?category .
                    }
            EOT;
        } else if ($category == 'SekarAgung') {
            return <<<EOT
            VALUES ?category { tb:$category }
            EOT;
        } else if (in_array($category, Arr::except($this->categories, 0), true)) {
            return <<<EOT
            VALUES ?category { tb:$category }
                    ?tembang a ?subCategory .
                    ?subCategory rdfs:subClassOf ?category .
            EOT;
        } else {
            return <<<EOT
            VALUES ?category { tb:$category }
                        ?category rdfs:subClassOf ?subCategory .
            EOT;
        }
    }

    private function getUsageTypeQuery(String $usageType = null)
    {
        if ($usageType) {
            return <<<EOT
            ?tembang tb:hasUsage ?usage .
                    ?usage a tb:$usageType .'
            EOT;
        } else {
            return null;
        }
    }

    private function getUsageQuery(String $usage = null)
    {
        if ($usage) {
            return <<<EOT
            ?tembang tb:hasUsage tb:$usage .
            EOT;
        } else {
            return;
        }
    }

    private function getMoodQuery(String $mood = null)
    {
        if ($mood) {
            return <<<EOT
            ?tembang tb:hasMood tb:$mood .
            EOT;
        } else {
            return;
        }
    }

    private function getRuleQuery(String $rule = null)
    {
        if ($rule) {
            return <<<EOT
            ?tembang tb:hasRule tb:$rule .
            EOT;
        } else {
            return null;
        }
    }
}
