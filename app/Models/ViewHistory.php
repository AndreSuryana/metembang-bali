<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ViewHistory extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'id'
    ];

    /**
     * Function to create new view history record.
     */
    public static function createNewViewHistory(Request $request, String $tembangUID)
    {
        try {
            ViewHistory::create([
                'tembang_uid'   => $tembangUID,
                'url'           => $request->url(),
                'user_id'       => auth('sanctum')->user()->id,
                'session_id'    => Session::getId(),
                'ip_address'    => $request->getClientIp() . ':' . $request->getPort(),
                'user_agent'         => $request->header('User-Agent')
            ]);
        } catch (Exception $e) {
            report($e);
        }
    }
}
