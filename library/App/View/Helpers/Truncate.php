<?php

class Zend_View_Helper_Truncate extends Zend_View_Helper_Abstract
{
    public function truncate( $string = '', $tamanho = 25, $caracter = '...' )
    {
        // Verificando se o tamanho da string e menor que o tamanho passado no parametro
        if( strlen( $string ) < $tamanho )
            // Se for menor, eu retorno a string inteira
            return $string;

        // Cortando a string de acordo com o parametro passado
        $stringTruncada = substr( $string, 0, $tamanho - 1 );
        // Concatenado com o caracter
        $stringTruncada .= $caracter;

        // Retornando a string truncada
        return $stringTruncada;
    }
}