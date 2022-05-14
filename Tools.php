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
		return ($nbTriples < 0)? "Error read the number of triples(see Debug)":$nbTriples." triples";
   }

    public static function loadData($endpoint,$urldata,$graph = "DEFAULT"){
        global $modeDebug,$modeVerbose;
        $endpoint->ResetErrors();
        // Adjust to find the revised manifest files in the andreawesterinen.github.io repository
        if (str_contains($urldata, "bordercloud.github")) {
            $url0 = str_replace("https://bordercloud.github.io", "https://andreawesterinen.github.io", $urldata);
        } elseif (str_contains($urldata, "www.w3.org")) {
            $url0 = str_replace("http://www.w3.org/2009/sparql/docs/tests", "https://andreawesterinen.github.io/rdf-tests/sparql11", $urldata);
        } else {
            $url0 = $urldata;
        }
        $newUrl = str_replace("manifest#", "", $urldata);
        if($graph == "DEFAULT"){
            $q = 'LOAD <'.$newUrl.'>';
        }else{
            $q = 'LOAD <'.$newUrl.'> INTO GRAPH <'.$graph.'>';
        }

        $res = $endpoint->queryUpdate($q);
        $err = $endpoint->getErrors();
        if ($err) {
            print_r($err);
            $success = false;
            $endpoint->ResetErrors();
        }
    }
}
