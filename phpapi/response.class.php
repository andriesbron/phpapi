class jsonResponse
{
    protected $errors=[];
    protected $warnings=[];
    protected $data=[];

    public function error($err)
    {
        $this->errors[] = $err;
    }

    public function warning($warn)
    {
        $this->warnings[] = $warn;
    }

    public function setData($data)
    {
        $this->data = $data;
    }
    public function mergeData($data)
    {
        $this->data = array_merge($this->data,$data);
    }
    public function respond()
    {
        header('Content-Type: application/json');
        $response = [
            "errors"  => $this->errors,
            "warnings"  => $this->warnings,
            "data" => $this->data
        ];
        if (count($this->errors) {
            $response["result"] = "failed";
        }
        else {
            $response["result"] = "success";
        }
        echo json_encode($response);
        exit(0);
    }
}

/* 
 * 
 * This class follows the explanation provided by pashak in the thread at
 * https://stackoverflow.com/questions/4809774/transfer-encoding-chunked-header-in-php
 * 
 */

/**
 * browsers collect first 1024 bytes
 * and show page only if bytes collected
 * so we will use space padding.
 * if you cannot understand what it means
 * check script with PADDING=0
 * 
 */
define("PADDING", 16);

/**
 * caret return and new line characters as constant
 */
define("RN", "\r\n");

/**
 * Chunked transfer states.
 */
define ("TRANSFER_INIT", 0);
define ("TRANSFER_OPEN", 1);
define ("TRANSFER_DONE", 2);

class chunked_transfer_helper
{
    private $content_type='text/html';
    private $file_name;
    private $transfer_state=TRANSFER_INIT;
    
    public function __construct($content_type, $file_name=False) {
        $this->content_type = $content_type;
        $this->file_name = $file_name;
    }
    
    /**
     * Send a space padding for browsers to collect 1kB of data. 
     * Typical use is browsers, e.g. to transfer straight into a visible browser window.
     */
    public function send_space_padding($padding=PADDING)
    {
        $padding_str = "";
        //+padding
        for($i=0; $i<PADDING; $i++){
            //64 spaces (1 block)
            $padding_str .= "                                                                ";
        }
        //current output buffer will shown immediately in browser
        //after this function
        $this->send_chunk($padding_str);
    }
    
    /**
     * Sends a chunk of data. Prior to using this  function, open_transfer should be called.
     * @param string $data
     * @return bool False if the communication is not open.
     */
    public function send_chunk($data):bool
    {
        switch ($this->transfer_state) {
            case TRANSFER_OPEN:
                echo print_r($data,TRUE);
                // Then
                $this->flush_data();
                break;
            
            case TRANSFER_DONE:
            case TRANSFER_INIT:
            default:
                return False;
                break;
        }
        return True;
    }
    
    public function done()
    {
        switch ($this->content_type) {
            case "text/html":
                echo RN;
                break;
            default:
                break;
        }
        echo "0\r\n\r\n";
        ob_flush();
        $this->transfer_state = TRANSFER_DONE;
    }
    
    public function open_transfer()
    {
        header('Transfer-Encoding: chunked');
        if ($this->file_name !== False) {
            header("Content-Type: application/download");
            header("Content-Disposition: attachment;filename={$this->file_name}");
        } else {
            header('Content-Type: '.$this->content_type);
        }
        header('Connection: keep-alive');
        $this->transfer_state = TRANSFER_OPEN;
    }
    
    /**
     * user function what get current output buffer data
     * and prefixes it with current buffer length.
     * next it call flush functions
     */
    private function flush_data()
    {
        $str=ob_get_contents();
        ob_clean();
        echo dechex(strlen($str)).RN.$str.RN;
        ob_flush();
        flush();
    }
}
