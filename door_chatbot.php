<?php 

//Settings
define('ROCKETCHAT_URL','https://rocket.chat');
define('BOT_USERNAME','bot');
define('BOT_PASSWORD','secretbotpassword');
define('BOT_CHANNEL','GENERAL');

$laststate = 1;
$lastpost = 0;
$lastopen = 0;
$lastchange = 0;

while(1)
{
  //read the door status from the GPIO Pin 0
  $val = trim(@shell_exec("/usr/bin/gpio read 0"));
  if($val!=$laststate)
  {
    $laststate = $val;
    $lastpost = time();
    $status = ($val?'closed':'open');
    echo time().';'.$val.';'.$status."\n";

    if($val==1 && $lastopen!=0)
      $duration = time()-$lastopen;
    else
    {
        $duration = -1;
        if($lastchange!=0)
           $open = 'Last use: `'.translateSecondsToNiceString((time()-$lastchange)).'` ago.';
        else $open = '';
    }

    $message = ':door: is now *'.$status.'*. '.($duration!=-1?'Was open for `'.translateSecondsToNiceString($duration).'`.':$open);

    sendRocket($message);

     if($val==0)
       $lastopen = time();
     $lastchange = time();
  }

  //wait 1 second to check the door status again
  sleep(1);
}


function sendRocket($message)
{
    $login = makeRequest(ROCKETCHAT_URL.'/api/login',array('password' => BOT_USERNAME, 'user' => BOT_USERNAME));
    $token = $login['data']['authToken'];
    $user = $login['data']['userId'];

    //join room
    makeRequest(ROCKETCHAT_URL.'/api/rooms/'.BOT_CHANNEL.'/join',array(),array('X-Auth-Token: '.$token,'X-User-Id: '.$user));

    //send message
    makeRequest(ROCKETCHAT_URL.'/api/rooms/'.BOT_CHANNEL.'/send',array('msg'=>$message),array('X-Auth-Token: '.$token,'X-User-Id: '.$user));
}

function makeRequest($url,$data,$headers=false,$post=true)
{
    $headers[] = 'Content-type: application/x-www-form-urlencoded';
    $options = array(
        'http' => array(
            'header'  => $headers,
            'method'  => $post?'POST':'GET',
            'content' => http_build_query($data)
        )
    );
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    if ($result === FALSE) { /* Handle error */ }

    return json_decode($result,true);
}

function translateSecondsToNiceString($secs)
{
    $units = array(
            "year"   => 365*24*3600,
            "month"   => 30*24*3600,
            "week"   => 7*24*3600,
            "day"    =>   24*3600,
            "hour"   =>      3600,
            "minute" =>        60,
            "second" =>        1,
    );
    
    if ( $secs == 0 ) return "0 seconds";
    $s = "";
    foreach ( $units as $name => $divisor ) {
            if ( $quot = intval($secs / $divisor) ) {
                    $s .= "$quot $name";
                    $s .= (abs($quot) > 1 ? "s" : "") . ", ";
                    $secs -= $quot * $divisor;
            }
    }
    return substr($s, 0, -2);
}