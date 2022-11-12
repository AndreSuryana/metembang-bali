<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\TembangSubmission;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserSubmissionController extends Controller
{
    /**
     * Display a listing of the user tembang submissions.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $submissions = [];

            foreach (Auth::user()->submissions as $submission) {

                unset($submission['user_id']);

                // Change cover path to cover url
                if ($submission->cover_path) {
                    $coverPath = $submission->cover_path;
                    $submission['cover_url'] = env('APP_URL') . $coverPath;
                    unset($submission['cover_path']);
                }

                // Change audio path to audio url
                if ($submission->audio_path) {
                    $audioPath = $submission->audio_path;
                    $submission['audio_url'] = env('APP_URL') . $audioPath;
                    unset($submission['audio_path']);
                }

                // Rule
                if ($submission->rule) {
                    $submission['rule'] = $submission->rule;
                    unset($submission->rule['tembang_submission_id']);
                }

                // Usages
                if ($submission->usages) {
                    $submission['usages'] = $submission->usages;
                    foreach ($submission['usages'] as $usage) {
                        unset($usage['tembang_submission_id']);
                    }
                }

                // Lyrics
                if ($submission->lyrics) {
                    $rawLyrics = $submission->lyrics;
                    $submission->lyrics = $this->lyricsToArray($rawLyrics);
                }

                // Lyrics IDN
                if ($submission->lyrics_idn) {
                    $rawLyricsIDN = $submission->lyrics_idn;
                    $submission->lyrics_idn = $this->lyricsToArray($rawLyricsIDN);
                }

                array_push($submissions, $submission);
            }

            return ResponseFormatter::success([
                'size' => count($submissions),
                'list' => $submissions
            ]);
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    /**
     * Create a new tembang submission.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // Validate request data with Validator
            $validator = Validator::make(
                $request->all(),
                [
                    'title' => 'required|string',
                    'category' => 'required|string',
                    'sub_category' => 'string|nullable',
                    'lyrics' => 'required|json',
                    'lyrics_idn' => 'nullable|json',
                    'meaning' => 'string|nullable',
                    'audio' => 'nullable|file|mimes:mp3,wav,m4a',
                    'cover' => 'nullable|image|mimes:jpg,jpeg,png',
                    'cover_source' => 'nullable|string',
                    'rule' => 'nullable|json',
                    'usages' => 'nullable|json',
                    'mood' => 'nullable'
                ]
            );

            // Check validator status
            if ($validator->fails()) {
                return ResponseFormatter::error(
                    $validator->errors()->first(),
                    409
                );
            }

            // Store new tembang submission
            $tembang = TembangSubmission::create([
                'user_id' => Auth::user()->id,
                'uid' => str_replace(' ', '_', strtolower($request->title)),
                'title' => $request->title,
                'category' => $request->category,
                'sub_category' => $request->sub_category,
                'lyrics' => implode(PHP_EOL, json_decode($request->lyrics)),
                'lyrics_idn' => $request->lyrics_idn ? implode(PHP_EOL, json_decode($request->lyrics_idn)) : null,
                'meaning' => $request->meaning,
                'mood' => $request->mood,
                'audio_path' => $request->audio ? $this->storeFile($request->audio, 'tembang/audio') : null,
                'cover_path' => $request->cover ? $this->storeFile($request->cover, 'tembang/image') : null,
                'cover_source' => $request->cover_source
            ]);

            // Rule
            if ($request->rule) {
                // Decode rule to json object
                $rule = json_decode($request->rule);

                // Insert rule to database
                DB::insert(
                    'INSERT INTO rules (
                            tembang_submission_id, 
                            name, 
                            guru_dingdong, 
                            guru_wilang, 
                            guru_gatra
                        ) VALUES (?, ?, ?, ?, ?)',
                    [
                        $tembang->id,
                        trim(str_replace(' ', '_', strtolower($rule->name))),
                        $rule->guru_dingdong,
                        $rule->guru_wilang,
                        $rule->guru_gatra
                    ]
                );
            }

            // Usages
            if ($request->usages) {
                // Decode usages to json object & loop through element
                foreach (json_decode($request->usages) as $usage) {
                    // Insert usage to database
                    DB::insert(
                        'INSERT INTO usages (
                                tembang_submission_id,
                                type,
                                activity
                            ) VALUES (?, ?, ?)',
                        [
                            $tembang->id,
                            $usage->type,
                            $usage->activity
                        ]
                    );
                }
            }

            // Change cover path to cover url
            if ($tembang->cover_path) {
                $coverPath = $tembang->cover_path;
                $tembang['cover_url'] = env('APP_URL') . $coverPath;
                unset($tembang['cover_path']);
            }

            // Change audio path to audio url
            if ($tembang->audio_path) {
                $audioPath = $tembang->audio_path;
                $tembang['audio_url'] = env('APP_URL') . $audioPath;
                unset($tembang['audio_path']);
            }

            // Format lyrics to array
            if ($tembang->lyrics) {
                $lyricsArray = $this->lyricsToArray($tembang->lyrics);
                $tembang['lyrics'] = $lyricsArray;
            }

            // Format lyrics idn to array
            if ($tembang->lyrics_idn) {
                $lyricsIDNArray = $this->lyricsToArray($tembang->lyrics_idn);
                $tembang['lyrics'] = $lyricsIDNArray;
            }

            return ResponseFormatter::success($tembang);
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }

    /**
     * Delete a tembang submission by id.
     * 
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            // Find submission by id
            $submission = TembangSubmission::findOrFail($id);

            // Delete file if exists
            if ($submission->cover_path) {
                Storage::delete($submission->cover_path);
            }

            if ($submission->audio_path) {
                Storage::delete($submission->audio_path);
            }

            // Delete the submission
            $submission->delete();

            return ResponseFormatter::success(true, 'Success to delete tembang submission.');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage());
        }
    }
}
