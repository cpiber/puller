<?php

namespace As247\Puller;

use As247\Puller\Exceptions\InvalidTokenException;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PullerController
{
    function messages(Request $request, PullerManager $pullerManager, Repository $config){
        $limit=$config->get('puller.time_limit',3600);
        set_time_limit($limit+1);
        $start = $keepalive_start = microtime(true);
        $keepalive_time=$config->get('puller.keepalive_time',10);

        $channel=$request->input('channel');
        $token=$request->input('token');
        $isPrivate=strpos($channel, 'private-')===0;
        if(!$channel){
            return response()->json(['error'=>'channel is required'],400);
        }
        $isNewToken=false;
        if(!$isPrivate){
            if(!$token){
                $token=$pullerManager->getToken($channel);
                $isNewToken=true;
            }
        }
        $sleep=$config->get('puller.sleep',1);
        try {
            // first pull here, to allow immediate error response
            $messages = $pullerManager->pull($channel, $token);
            return response()->stream(function () use ($pullerManager, $channel, $token, $isNewToken, $sleep, $limit, $start, $keepalive_start, $keepalive_time, $messages): void {
                while (ob_get_level()) ob_end_clean();
                do{
                    if($message=$messages->last()){
                        $token=$message->token;
                    }
                    $messages=$messages->map(function ($message){
                        return $message->payload;
                    });
                    if($isNewToken || !$messages->isEmpty() || connection_aborted()){
                        break;
                    }
                    if($sleep>0){
                        if($sleep<1){
                            usleep($sleep*1000000);
                        }else{
                            sleep(1);
                        }
                    }
                    $messages = $pullerManager->pull($channel, $token);
                    if (microtime(true) - $keepalive_start >= $keepalive_time) {
                        $keepalive_start = microtime(true);
                        Log::debug("Puller keepalive sent for channel {channel}", ['channel'=>$channel]);
                        // keep connection alive and check for aborted connection
                        // sent string needs to be long enough to avoid buffering by proxies
                        echo str_repeat("\r", 4096);
                        flush();
                    }
                }while((microtime(true)-$start)<$limit);

                $data = json_encode(['messages' => $messages,'token'=>$token]);
                echo $data;
                flush();
            }, 200, ['Content-Type' => 'application/json', 'X-Accel-Buffering' => 'no']);
        }catch (InvalidTokenException $exception){
            return response()->json(['error'=>$exception->getMessage()],401);
        }
    }
}

