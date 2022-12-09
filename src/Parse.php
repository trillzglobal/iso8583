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

                $this->socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
                $ret = socket_connect($this->socket, $this->host, $this->port);
                
            // $this->socket=fsockopen($this->host, $this->port, $errno, $errstr, 30);
            if($ret){
                $this->state="open";
                $this->colourLog("Socket Connecte Successfully to  : ".$this->host." on port ".$this->port."\n\n", 'success');
                
            }
            else{
                $this->colourLog("Connection Failed to : ".$this->host." on port ".$this->port."  with error \n\n", 'error');
            }

    }


    function sendCommand($payload){
        $this->payload = $payload;
        if($this->state=="closed")return false;

        // $header = $this->getheader(strlen($payload));
        $full = $payload;

        if($payload == false){
            $this->colourLog("Message format Not Supported : ".$this->payload." \n", 'error', "Error");
            return "Message format Not Supported";

        }
        $this->sendPDU($full);
        $response=$this->readPDU();

        return $response;
    
    }

    function getheader($string){
        $this->colourLog("Header String: $string ", "info", "[x]");
        $headerbin= decbin((int)$string);
        $this->colourLog("Header BIN: $headerbin ", "info", "[x]");
        $bin = str_pad($headerbin,16,"0",STR_PAD_LEFT);
        $this->colourLog("BIN: $bin ", "info", "[x]");

        $unpack =  $this->bitconvert($bin);
        return pack("H*", $unpack);
    }



    function sendPDU($payload){
        
        $resp = socket_write($this->socket, $payload, strlen($payload));

        $this->colourLog("Sending Payload : $payload", 'success', "SEND ISO");
        $this->colourLog("Sent Response: $resp", 'success', "SEND ISO");
        
        
    }

    function bitconvert($data){
        $i =0;
        $d = 4;
        $length = strlen($data);
        $hex = "";
        while($i < $length){
            $bit = substr($data, $i, $d);
            $hex = $hex.base_convert($bit,2,16);
            $i = $i +4;
        }
        
        return $hex;
    }


    function readPDU(){
        $value = socket_read($this->socket, 1024);
        $this->colourLog("Read Header : $value ", "Success", "Warning");

        if(empty($value)){
            return false;
            $this->colourLog("PDU Reading produce no result", "warning", "Info");
        }
            $decrypt = $this->decryptIso($value);
            return json_encode(["ISO_value "=>$value, "decrypt" =>$decrypt ]);
     
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
        return $arr;
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