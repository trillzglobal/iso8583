<?php

namespace Trillzglobal\Iso8583;

use Andromeda\ISO8583\ExampleMessage;
use Andromeda\ISO8583\Parser;

class IsoContract extends Parse{


    public function packIso8583(array $data){

        $message = new ExampleMessage();
        $isoMaker = new Parser($message);

        $isoMaker->addMTI($data["0"]);
        unset($data["0"]);
        foreach($data as $key=>$value){
            $isoMaker->addData($key, $value);
        }
        $message = $isoMaker->getISO();
        $header =  $this->getheader(strlen($message));
        return $header.$message;
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

    function getheader($string){
        echo "\n Header String: $string \n";
        $headerbin= decbin((int)$string);
        echo "\n Header BIN: $headerbin \n";
        $bin = str_pad($headerbin,16,"0",STR_PAD_LEFT);
        echo "\n BIN: $bin \n";

        $unpack =  $this->bitconvert($bin);
        return pack("H*", $unpack);
    }
}