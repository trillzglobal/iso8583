<?php

namespace Trillzglobal\Iso8583;

class Field_127{
   public $array_field127 = [];

    public function __construct(array $data)
    {
        $this->array_field127 = $data;
       
    }

    private function bitconvert($data){
        $i =0;
        $d = 4;
        $length = strlen($data);
        $hex = "";
        while($i < $length){
            $bit = substr($data, $i, $d);
            $hex = $hex.base_convert($bit,2,16);
            $i = $i +4;
        }
        $bitmap = hex2bin($hex);
        return $bitmap;
    }

    function createBitmap($data){

        $zerobit = str_repeat("0",64);
        $zerobit = substr_replace($zerobit,"1",0,1);
    
       $data = $data;
       foreach($data as $key=>$value){
        if($key != "0"){
            $zerobit = substr_replace($zerobit,"1",(int)$key-1,1);
        }
       }
       return $zerobit;
    }

    function _pack($field127){
        $packed = "";
        if(!is_array($field127))return false;
        $binary =  $this->createBitmap($field127);
        $packed = $packed.$this->bitconvert($binary);
        $load  = $field127;
        unset($load["0"]);
        ksort($load);
        foreach($load as $key=>$value){
            $packed = $packed.$value;
     
        }
    
        return $packed;
    }

    public function get127(){
        return $this->_pack($this->array_field127);
    }
}