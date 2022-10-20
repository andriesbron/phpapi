<?php

class phpapi
{
    public static function isonline($host, $port=80)
    {
      $online=False;
      $waitTimeoutInSeconds=1;
      if ($fp=fsockopen(gethostbyname($url), $port, $errCode, $errStr, $waitTimeoutInSeconds)){
        $online=True;
      }
      fclose($fp);
      return $online;
    }
  
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
