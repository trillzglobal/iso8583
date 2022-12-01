<?php

namespace Trillzglobal\Iso8583;

use Andromeda\ISO8583\ExampleMessage;
use Andromeda\ISO8583\Parser;

class Parse {

    public $host;
    public $port;
    public $state="close";
    public $debug = true;
    public $socket;

    public function __construct($host, $port){
            $this->host = $host;
            $this->port =  $port;
    }
    
    public function connect(){

            $this->socket=fsockopen($this->host, $this->port, $errno, $errstr, 30);
            if($this->socket){
                $this->state="open";
                $this->colourLog("Socket Connecte Successfully to  : ".$this->host." on port ".$this->port."\n\n", 'success');
                
            }
            else{
                $this->colourLog("Connection Failed to : ".$this->host." on port ".$this->port."  with error $errstr\n\n", 'error');
            }

    }


    function sendCommand($payload){
        $this->payload = $payload;
        if($this->state=="closed")return false;

       
        if($payload == false){
            $this->colourLog("Message format Not Supported : ".$this->payload." \n", 'error', "Error");
            return "Message format Not Supported";

        }
        $this->sendPDU($payload);
        $response=$this->readPDU();

        return $response;
    
    }


    function sendPDU($payload){
        
        $resp = fwrite($this->socket, $payload, strlen($payload));

        if($this->debug){
            $this->colourLog("Sending Payload : $resp", 'success', "SEND ISO");
        }
        
    }

  

    function readPDU(){
        $value = fread($this->socket, 1000);
        $this->colourLog("Read Header : $value ", "Success", "Warning");

        if(empty($value)){
            return false;
            $this->colourLog("PDU Reading produce no result", "warning", "Info");
        }
            $this->decryptIso($value);
            return $value;
     
    }


    function decryptIso($payload){
        $message = new ExampleMessage();
        $isoMaker = new Parser($message);
        $isoMaker->addMessage($payload);
        $isoMaker->validateISO();
        $bitmap = $isoMaker->getBitmap();
        $this->colourLog("BitMap: ".$bitmap, "warning", "Warning");
        $chars = str_split($bitmap);
        $arr = [];
        $i = 1;
        foreach($chars as $char){

            if($char == "1")array_push($arr,["$i"=>$isoMaker->getBit($i)]);
            $i++;
        }
        $this->colourLog("Iso: ".json_encode($arr), "warning", "Warning");
    }




    private function colourLog($str, $type = 'i', $start="Info"){
        if($this->debug){

            switch ($type) {
                case 'error': 
                    echo "$start : \033[31m$str \033[0m\n";
                break;
                case 'success': //success
                    echo "$start : \033[32m$str \033[0m\n";
                break;
                case 'warning': //warning
                    echo "$start : \033[33m$str \033[0m\n";
                break;  
                case 'info': //info
                    echo "$start :  \033[36m$str \033[0m\n";
                break;      
                default:
                   echo "$start : $str \n";
                break;
            }
        }
    }


    public function hexconvert($data){
        $i =0;
        $d = 1;
        $length = strlen($data);
        $bit = "";
        while($i < $length){
            $hex = substr($data, $i, $d);
            
            $unpad = base_convert($hex,16,2);
            $padded = str_pad($unpad,4,"0",STR_PAD_LEFT);
            $bit = $bit.$padded;
            $i++;
        }
        
        return $bit;
    }
}