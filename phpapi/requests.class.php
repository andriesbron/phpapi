
class requests
{
    /**
     * @desc Checks if a server is online. Default check is on port 80.
     * @param $host url or host to verify.
     * @param $port port to check
     */
    public static function isOnLine($url, $port=80)
    {
        // @todo Check if an ip address was provided or not
        $url_parts=parse_url($url);
        
        $online=False;
        $waitTimeoutInSeconds=1;
        $errCode=False;
        $errStr=False;

        if ($fp=fsockopen(gethostbyname($url_parts['host']), $port, $errCode, $errStr, $waitTimeoutInSeconds)){
        $online=True;
        }
        fclose($fp);

        return $online;
    }
    
    /**
     * @desc from stackoverflow Can't remember where...
     */
    public function getRedirectsToUri($uri)
    {
        $redirects = array();
        $http = stream_context_create();
        stream_context_set_params(
            $http,
            array(
                "notification" => function() use (&$redirects)
                {
                    if (func_get_arg(0) === STREAM_NOTIFY_REDIRECTED) {
                        $redirects[] = func_get_arg(2);
                    }
                }
            )
        );
        file_get_contents($uri, false, $http);
        return $redirects;
    }
}
