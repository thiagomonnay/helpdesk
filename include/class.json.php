<?php
/*********************************************************************
    class.json.php

    Parses JSON text data to PHP associative array. Useful mainly for API
    JSON requests. The module will attempt to use the json_* functions
    builtin to PHP5.2+ if they exist and will fall back to a pure-php
    implementation included in JSON.php.

    Jared Hancock
    Copyright (c)  2006-2010 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
    $Id: $
**********************************************************************/

include_once "JSON.php";

class JsonDataParser {
    function parse($stream) {
        $contents = '';
        while (!feof($stream)) {
            $contents .= fread($stream, 8192);
        }
        if (function_exists("json_decode")) {
            return json_decode($contents, true);
        } else {
            # Create associative arrays rather than 'objects'
            $decoder = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
            return $decoder->decode($contents);
        }
    }
    function lastError() {
        if (function_exists("json_last_error")) {
            $errors = array(
            JSON_ERROR_NONE => 'Sem erros',
            JSON_ERROR_DEPTH => 'Profundidade máxima da pilha ultrapassado',
            JSON_ERROR_STATE_MISMATCH => 'Underflow ou os modos incompatibilidade',
            JSON_ERROR_CTRL_CHAR => 'Caractere de controle inesperado encontrado',
            JSON_ERROR_SYNTAX => 'Erro de sintaxe, JSON',
            JSON_ERROR_UTF8 => 'UTF-8 malformado, possivelmente codificado incorretamente'
            );
            if ($message = $errors[json_last_error()])
                return $message;
            return "Erro desconhecido";
        } else {
            # Doesn't look like Servies_JSON supports errors for decode()
            return "Erro de análise JSON desconhecido";
        }
    }
}

class JsonDataEncoder {
    function encode($var) {
        $decoder = new Services_JSON();
        return $decoder->encode($var);
    }
}
