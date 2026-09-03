<?php

class View {
    private static Template $baseTemplate;


    public static function getBaseTemplate(): Template {
        return self::$baseTemplate;
    }


    public static function setBaseTemplate (Template $baseTemplate) : void{
        self::$baseTemplate = $baseTemplate;
    }


    public static function PrintFinalTemplate(?array $headers = null): void {
        global $cfg;

        if($headers !== null){
            foreach($headers as $header){
                header($header);
            }
        }
        header('Content-type:' . $cfg['defaultContentType']);
        print self::$baseTemplate->Render(true);
    }
}