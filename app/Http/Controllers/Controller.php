<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use EasyRdf\Sparql\Client;
use EasyRdf\RdfNamespace;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $sparql;

    function __construct()
    {
        RdfNamespace::set('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
        RdfNamespace::set('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');
        RdfNamespace::set('owl', 'http://www.w3.org/2002/07/owl#');
        RdfNamespace::set('tb', 'http://www.semanticweb.org/andresuryana/ontologies/2022/8/tembang-bali#');

        $this->sparql = new Client(env('APP_JENA', 'http://localhost:3030/tembang-bali/'), env('APP_JENA', 'http://localhost:3030/tembang-bali/') . 'update');
    }

    public function parseData($data, $isRaw = false)
    {
        $result = explode('#', $data);
        $result = $result[count($result) - 1];

        if (!$isRaw) {
            $result = substr(preg_replace('/(?<!\ )[A-Z]/', ' $0', $result), 1);
            $result = explode('-', $result);
            $result = $result[0];
        }

        return $result;
    }

    public function storeFile($file, $storePath)
    {
        // If not authenticated and file null then return
        if (!Auth::check() && $file == null) {
            return null;
        }

        // Otherwise store the file
        $fileName = sprintf("%s-%s.%s", Carbon::now()->toDateString(), time(), $file->getClientOriginalExtension());
        $path = $file->storeAs($storePath, $fileName, 'public');

        return 'storage/' . $path;
    }

    public function lyricsToArray($lyrics)
    {
        return preg_split('/\r\n|\n|\r/', $lyrics);
    }

    public function arrayToLyrics($lyrics)
    {
        if ($lyrics == null) return;
        if (empty($lyrics)) return;

        return preg_replace('/\r\n|\n|\r/', '\n$0', $lyrics);
    }
}
