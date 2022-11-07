<?php

namespace App\Http\Controllers;

use App\Models\TembangSubmission;
use Exception;
use App\Helpers\ResponseFormatter;
use Carbon\Carbon;
use EasyRdf\Http\Exception as RdfHttpException;
use Illuminate\Http\Request;

class TembangSubmissionController extends Controller
{
    /**
     * Display a listing of the all tembang submissions.
     * 
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $submissions = [];

            foreach (TembangSubmission::all() as $submission) {
                // Rule
                if ($submission->rule) {
                    $submission['rule'] = $submission->rule;
                }

                // Usages
                if ($submission->usages) {
                    $submission['usages'] = $submission->usages;
                }

                array_push($submissions, $submission);
            }

            return ResponseFormatter::success($submissions);
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    /**
     * Display a tembang submission by id.
     * 
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $submission = TembangSubmission::find($id);
            $submission['rule'] = $submission->rule;
            $submission['usages'] = $submission->usages;

            return ResponseFormatter::success($submission);
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    /**
     * Accept and store a tembang submission to ontology.
     *
     * @return \Illuminate\Http\Response
     */
    public function accept(Request $request, $id)
    {
        try {
            // Get user's tembang submission by id
            $tembang = TembangSubmission::find($id);

            // Check tembang submission status, return error if status = accepted/rejected
            if (in_array($tembang->status, ['accepted', 'rejected'], true)) {
                if ($request->expectsJson()) {
                    return ResponseFormatter::error("Submission status is already $tembang->status", 409);
                } else {
                    return redirect()->back()->with([
                        'error' => "Submission status is already $tembang->status"
                    ]);
                }
            }

            // Check if tembang already exists (by individuals name)
            $result = $this->sparql->query("
                SELECT DISTINCT ?tembang WHERE {
                    ?tembang a tb:TembangBali .
                    FILTER(?tembang = tb:$tembang->uid)
                } LIMIT 1
            ");

            // If there is any individuals with name same as submission uid,
            // then return error with message
            if ($result->numRows() > 0) {
                if ($request->expectsJson()) {
                    return ResponseFormatter::error("The Balinese song with the individual name '$tembang->uid' already exists in the ontology data", 409);
                } else {
                    return redirect()->back()->with([
                        'error' => "The Balinese song with the individual name '$tembang->uid' already exists in the ontology data"
                    ]);
                }
            }

            // Prepare query
            $insertQueryRule = $this->getInsertQueryRule($tembang->rule, $tembang->uid);
            $insertQueryUsages = $this->getInsertQueryUsages($tembang->usages, $tembang->uid);
            $insertQueryTembang = $this->getInsertQueryTembang($tembang);

            // dd($insertQueryUsages);

            // Insert query
            $query = <<<EOT
                INSERT DATA {
                    $insertQueryRule
                    $insertQueryUsages
                    $insertQueryTembang
                }
            EOT;

            // Insert tembang data
            $insert = $this->sparql->update($query);

            if ($insert->isSuccessful()) {
                $tembang->update([
                    'status' => 'accepted'
                ]);

                if ($request->expectsJson()) {
                    return ResponseFormatter::success(true);
                } else {
                    return redirect()->back()->with([
                        'success' => 'Data tembang ' . $tembang->title . ' berhasil ditambahkan ke ontologi'
                    ]);
                }
            }
        } catch (RdfHttpException $e) {
            if ($request->expectsJson()) {
                return ResponseFormatter::error($e->getMessage());
            } else {
                return redirect()->back()->with([
                    'error' => 'Data tembang ' . $tembang->title . ' gagal ditambahkan ke ontologi'
                ]);
            }
        }
    }

    /**
     * Reject a tembang submission.
     * 
     * @return \Illuminate\Http\Response
     */
    public function reject(Request $request, $id)
    {
        try {
            // Get user submission by id
            $submission = TembangSubmission::find($id);

            $submission->update([
                'status' => 'rejected'
            ]);

            if ($request->expectsJson()) {
                return ResponseFormatter::success(true);
            } else {
                return redirect()->back()->with([
                    'success' => 'Submission tembang ' . $submission->title . ' berhasil ditolak'
                ]);
            }
        } catch (Exception $e) {
            if ($request->expectsJson()) {
                return ResponseFormatter::error($e->getMessage());
            } else {
                return redirect()->back()->with([
                    'error' => 'Submission tembang ' . $submission->title . ' gagal ditolak'
                ]);
            }
        }
    }

    /**
     * Get insert query for rule.
     * 
     * @return string
     */
    private function getInsertQueryRule($rule, $tembangUID)
    {
        // Return if rule is null
        if ($rule == null) return;

        // Check if rule is exists
        $result = $this->sparql->query("
            SELECT ?rule WHERE {
                ?rule a tb:Rule ;
                    tb:hasGuruDingdong '$rule->guru_dingdong' ;
                    tb:hasGuruWilang '$rule->guru_wilang' ;
                    tb:hasGuruGatra $rule->guru_gatra .
            } LIMIT 1
        ");

        // Init query
        $query = null;

        if ($result->numRows() > 0) {
            // Get rule
            $name = $this->parseData($result[0]->rule, true);

            $query = "tb:$tembangUID tb:hasRule tb:$name .\n";
        } else {
            // Rule individu name
            $name = trim(str_replace(' ', '_', strtolower($rule->name)));

            $query = <<<EOT
                tb:$name a tb:Rule ;
                    tb:hasGuruDingdong '$rule->guru_dingdong' ;
                    tb:hasGuruWilang '$rule->guru_wilang' ;
                    tb:hasGuruGatra $rule->guru_gatra .
                tb:$tembangUID tb:hasRule tb:$name .
            EOT;
        }

        return $query;
    }

    /**
     * Get insert query for usages.
     * 
     * @return string
     */
    private function getInsertQueryUsages($usages, $tembangUID)
    {
        // Return if usages is null
        if ($usages == null) return;

        // Init query
        $query = null;

        foreach ($usages as $usage) {
            // Check if usages is exists
            $result = $this->sparql->query("
                SELECT ?usage WHERE {
                    ?usage a tb:Usage ;
                        a tb:$usage->type ;
                        tb:hasActivity '$usage->activity' .
                } LIMIT 1
            ");

            if ($result->numRows() > 0) {
                // Get usage
                $usage = $this->parseData($result[0]->usage, true);

                $query .= <<<EOT
                    tb:$tembangUID tb:hasUsage tb:$usage .
                EOT;
            } else {
                // Usage individu
                $name = trim(str_replace(' ', '_', strtolower($usage->activity)));
                $type = trim(str_replace(' ', '', $usage->type));

                $query .= <<<EOT
                    tb:$name a tb:$type ;
                        tb:hasActivity '$usage->activity' .
                    tb:$tembangUID tb:hasUsage tb:$name . \n
                EOT;
            }
        }

        return $query;
    }

    /** 
     * Get insert query for tembang.
     * 
     * @return string
     */
    private function getInsertQueryTembang($tembang)
    {
        $query = null;
        $subCategory = trim(str_replace(' ', '', $tembang->sub_category));
        $dateAdded = Carbon::now()->format('Y-m-d\TH:i:s');
        $author = $tembang->user->name;

        // initial
        $lyrics = str_replace("\r\n", "↵", $tembang->lyrics);
        $query = <<<EOT
            tb:$tembang->uid a tb:TembangBali ;
                a tb:$tembang->category ;
                tb:hasTitle '$tembang->title' ;
                tb:hasAuthor '$author' ;
                tb:hasLyrics '$lyrics'@ban ;
                tb:hasDateAdded '$dateAdded' .\n
        EOT;

        // sub category
        if ($tembang->sub_category) {
            $query .= <<<EOT
                tb:$tembang->uid a tb:$subCategory .\n
            EOT;
        }

        // lyrics idn
        if ($tembang->lyrics_idn) {
            $lyrics_idn = str_replace("\r\n", "↵", $tembang->lyrics_idn);
            $query .= <<<EOT
                tb:$tembang->uid tb:hasLyrics '$lyrics_idn'@id .\n
            EOT;
        }

        // meaning
        if ($tembang->meaning) {
            $query .= <<<EOT
                tb:$tembang->uid tb:hasMeaning '$tembang->meaning' .\n
            EOT;
        }

        // mood
        if ($tembang->mood) {
            $query .= <<<EOT
                tb:$tembang->uid tb:hasMood '$tembang->mood' .\n
            EOT;
        }

        // audio path
        if ($tembang->audio_path) {
            $query .= <<<EOT
                tb:$tembang->uid tb:hasAudioPath '$tembang->audio_path' .\n
            EOT;
        }

        // cover path
        if ($tembang->cover_path) {
            $query .= <<<EOT
                tb:$tembang->uid tb:hasCoverPath '$tembang->cover_path' .\n
            EOT;
        }

        // cover source
        if ($tembang->cover_source) {
            $query .= <<<EOT
                tb:$tembang->uid tb:hasCoverSource '$tembang->cover_source' .\n
            EOT;
        }

        return $query;
    }
}
