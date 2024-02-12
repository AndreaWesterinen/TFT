<?php

class Tools
{
    public static function count(){
		global $modeDebug,$modeVerbose,$ENDPOINT;

		$ENDPOINT->ResetErrors();
		$q = 'SELECT (COUNT(?s) AS ?count) WHERE {GRAPH ?g {  ?s ?p ?v .}} ';
		$res = $ENDPOINT->query($q, 'row');
		$err = $ENDPOINT->getErrors();
		if ($err) {
			return -1;
		}
		return $res["count"]; //todo trycatch //test with sesame */
   }

    public static function printNbTriples(){
		$nbTriples = Tools::count();
		return ($nbTriples < 0)? "Error reading the number of triples (see Debug)":$nbTriples." triples";
   }

    public static function loadData($endpoint,$urldata,$graph = "DEFAULT"){
        global $modeDebug,$modeVerbose;
        $endpoint->ResetErrors();
        $newUrl = str_replace("manifest#", "", $urldata);
        
        if($graph == "DEFAULT"){
            $q = 'LOAD <'.$newUrl.'>';
        }else{
            $q = 'LOAD <'.$newUrl.'> INTO GRAPH <'.$graph.'>';
        }
        $endpointWrite = $endpoint->getEndpointWrite();
        // Make sure that the endpoint is "update" and not "query" - which is set for SERVICE endpoints
        $endpoint->setEndpointWrite(str_replace("/query", "/update", $endpointWrite));
        $res = $endpoint->queryUpdate($q);
        $err = $endpoint->getErrors();
        if ($err) {
            print_r($err);
            $success = false;
            $endpoint->ResetErrors();
        }
    }
}
