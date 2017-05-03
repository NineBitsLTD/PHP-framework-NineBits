<?php

/**
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Helper\File;

class ParserPHP extends \Core\Object {
    
    public function __construct(){
        parent::__construct([
            0=>'',
            1=>'Folder %s not found.',
            2=>'File %s not found.',
            3=>'File %s is not php.',
            4=>'File %s is very long.',
        ]);
    }
    
    public function ParseInfo($info){
        $result = [
            'title' => '',
            'description' => '',
            'uses' =>'',
        ];
        preg_match_all("!\n\s*\**\s*(\S+.+)\n!", $info, $matches);
        if(count($matches[1])>0) $result['title'] = array_shift($matches[1]);
        if(count($matches[1])>0) $result['description'] = implode("\n", $matches[1]);
        return $result;
    }

    /**
     * Получить список методов и классов в файле
     * 
     * @param type $filename
     * @return array
     */
    public function GetInfo($filename){
        $classes = null;
        if($this->CheckPhpFile($filename)==0){
            $content = file_get_contents($filename);
            $tokens = token_get_all($content);
            foreach ($tokens as $key=>$token) {
                if(!is_array($token)) unset($tokens[$key]);
                else if($token[0]==T_WHITESPACE || $token[0]==T_PUBLIC || $token[0]==T_STATIC || $token[0]==T_PRIVATE || $token[0]==T_PROTECTED) unset($tokens[$key]);
            }
            $classes=[];
            $variables=[];
            $functions=[];
            $test=[];
            reset($tokens);
            $comment="";
            while (list($key, $token) = each($tokens)) {
                $next = current($tokens);
                if($token[0]==T_CLASS && isset($next) && $next[0]==T_STRING){
                    if(count($classes)>0) current($classes)['properties'] = $variables;
                    if(count($classes)>0) current($classes)['methods'] = $functions;
                    $classes[] = [
                        'file' => $filename,
                        'name' => $next[1],
                        'info' => $this->ParseInfo($comment),
                        'properties'=>[],
                        'methods'=>[],
                    ];
                    $variables = [];
                    $functions = [];
                    $comment = "";
                } else if($token[0]==T_FUNCTION && isset($next) && $next[0]==T_STRING){
                    $functions[] = [
                        'name' => $next[1],
                        'info' => $this->ParseInfo($comment),
                    ];
                    $comment = "";
                } else if($token[0]==T_DOC_COMMENT && isset($next) && $next[0]==T_VARIABLE){
                    $variables[] = [
                        'name' => substr($next[1],1),
                        'info' => $this->ParseInfo($token[1]),
                    ];
                    $comment = "";
                } else if($token[0]==T_DOC_COMMENT){
                    $comment = $token[1];
                } else $comment = "";
            }
            if(count($classes)>0) {
                end($classes);
                $classes[key($classes)]['properties'] = $variables;
                $classes[key($classes)]['methods'] = $functions;
            }
        }
        return $classes;
    }
    /**
     * Проверяет, является ли $filename файлом PHP
     * 
     * @param string $filename
     * @return int Если проверка прошла успешно то возвращает код 0.
     */
    public function CheckPhpFile($filename){
        if(!is_file($filename)) return 2;
        $info = pathinfo($filename);
        if(strtolower($info['extension']) != 'php') return 3;
        if(filesize($filename)>10485760) return 4;
        return 0;
    }
    /**
     * 
     * @param type $path
     * @return type
     */
    public function GetListPhpFilename($path){
        if(!is_dir($path)) $this->ErrorMsg(sprintf($this->ErrorList[1], $path), __FILE__, __LINE__);
        $result = [];
        foreach (scandir($path) as $key => $value) {
            $file = $path."/".$value;
            $info = $this->GetInfo($file);
            if(isset($info)) $result[]=$info;
        }
        return $result;
    }
}
