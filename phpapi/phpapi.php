<?php

class debug
{
    private $level=0; // 0=None
    
    public function setLevel($level)
    {
        $this->level = $level;
    }
    
    public function p($data, $level, $die=False)
    {
        if ($this->level > 0) {
            if ($level <= $this->level) { 
                echo "<pre>";
                print_r($data);
                echo "</pre>";
                if ($die) die;
            }
        }
    }   
}

class html
{
    /**
     * @param $name a html element with a name attribute that has the value of $name
     * @param $xpath = new DOMXPath($xmlDoc)
     */
    public static function getElementByName($name, $xpath) 
    {
        return $xpath->query("//*[@name='$name']")->item(0);
    }
    
    /*
     * @param $id value of an id attribute
     * @param $xpath = new DOMXPath($xmlDoc);
     */
    public static function getElementById($id, $xpath) 
    {
        return $xpath->query("//*[@id='$id']")->item(0);
    }   
    
    
}

class requests
{
    /**
     * @desc Checks if a server is online. Default check is on port 80.
     * @param $host url or host to verify.
     * @param $port port to check
     */
    public static function isOnLine($host, $port=80)
    {
        // @todo Check if an ip address was provided or not
        $url_parts=parse_url($host);
        
        $online=False;
        $waitTimeoutInSeconds=1;
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

class math
{
     /**
        @attention Did not test this function.
        @brief Calculates whether an unsigned int contains an even number of bits set to '1'.
               Whether it is the original source or not, I took it from stackoverflow:
               https://stackoverflow.com/questions/8871204/count-number-of-1s-in-binary-representation/8871435#8871435
               Explanation here:
               https://stackoverflow.com/questions/19729466/how-to-find-number-of-1s-in-a-binary-number-in-o1-time
        @param integer $aui_u Any 16 bits unsigned integer.
        @return Boolean True if the total number of bits set is an even number.
    */
    public function bitParityOfUnsignedIntIsEven($aui_u)
    {
         $uCount = $aui_u - (($aui_u >> 1) & 033333333333) -
                            (($aui_u >> 2) & 011111111111);
         /**
             The number of bits is given by this number:
             (($uCount + ($uCount >> 3)) & 030707070707) % 63)
         */
      
         return (((($uCount + ($uCount >> 3)) & 030707070707) % 63) %2 == 0);
    }
}


class randomMath
{
    
    /**
     * Generate a random string, using a cryptographically secure
     * pseudorandom number generator (random_int)
     *
     * For PHP 7, random_int is a PHP core function
     * For PHP 5.x, depends on https://github.com/paragonie/random_compat
     *
     * @param int $length      How many characters do we want?
     * @param string $keyspace A string of all possible characters
     *                         to select from
     * @return string
     */
    static public function random_str(
        $length,
        $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
        ) 
    {
        $str = '';

        $max = mb_strlen($keyspace, '8bit') - 1;
        if ($max < 1) {
                throw new Exception('$keyspace must be at least two characters long');
        }
        for ($i = 0; $i < $length; ++$i) {
                $str .= $keyspace[random_int(0, $max)];
        }

        return $str;
    }
}




