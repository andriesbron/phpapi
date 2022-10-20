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
