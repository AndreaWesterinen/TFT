<?php

class QueryEvaluationTest {

	function countAllTests(){
		global $modeDebug,$modeVerbose,$ENDPOINT,$GRAPHTESTS ;

		$ENDPOINT->ResetErrors();
		$q = Test::PREFIX.'
		SELECT (COUNT(?s) AS ?count) WHERE {
			GRAPH <'.$GRAPHTESTS.'> { ?s a mf:QueryEvaluationTest ;
							 dawgt:approval dawgt:Approved.}} ';
		$res = $ENDPOINT->query($q, 'row');
		$err = $ENDPOINT->getErrors();
		if ($err) {
			return -1;
		}
		return $res["count"];
	}
	function countSkipTests(){
		global $modeDebug,$modeVerbose,$ENDPOINT,$GRAPHTESTS;

		$ENDPOINT->ResetErrors();
		$q = Test::PREFIX.'
		SELECT (COUNT(?s) AS ?count) WHERE {
			GRAPH <'.$GRAPHTESTS .'> { ?s a mf:QueryEvaluationTest ;
							 dawgt:approval dawgt:NotClassified .}} ';
		$res = $ENDPOINT->query($q, 'row');
		$err = $ENDPOINT->getErrors();
		if ($err) {
			return -1;
		}
		return $res["count"];
	}
    static function countApprovedTests(){
		global $modeDebug,$modeVerbose,$ENDPOINT,$GRAPHTESTS;

		$ENDPOINT->ResetErrors();
		$q = Test::PREFIX.'
		SELECT (COUNT(DISTINCT ?s) AS ?count) WHERE {
			GRAPH <'.$GRAPHTESTS .'> { ?s a mf:QueryEvaluationTest ;
							 dawgt:approval dawgt:Approved .}} ';
		$res = $ENDPOINT->query($q, 'row');
		$err = $ENDPOINT->getErrors();
		if ($err) {
			return -1;
		}
		return $res["count"];
   }

	static function doAllTests(){
		global $modeDebug,$modeVerbose,$ENDPOINT,$CURL,$GRAPHTESTS,$GRAPH_RESULTS_EARL,$TAGTESTS;
		 //////////////////////////////////////////////////////////////////////
		echo "
--------------------------------------------------------------------
TESTS : QueryEvaluationTest";// ( ".QueryEvaluationTest::countApprovedTests()." Approved, ".QueryEvaluationTest::countSkipTests()." Skipped, ".QueryEvaluationTest::countAllTests()." Total\n";
		$Report = new TestsReport("QueryEvaluationTest",$TAGTESTS.'-QueryEvaluationTest-junit.xml');
		$q = Test::PREFIX.' 
SELECT DISTINCT ?testiri ?name ?queryTest 
?dataInput ?dataOutput ?graphDataInput ?serviceDataInput
?testSkipped
WHERE {
    GRAPH <'.$GRAPHTESTS .'> {
     #VALUES ?testiri {<http://www.w3.org/2009/sparql/docs/tests/data-sparql11/csv-tsv-res/manifest#tsv02>}
     #    ?manifest   a  mf:Manifest ;
     #               mf:entries ?collection .
     #   ?collection rdf:rest*/rdf:first ?testiri .

		?testiri a  mf:QueryEvaluationTest ;
				 mf:name ?name ;
				 dawgt:approval dawgt:Approved ;
				 mf:action [ 
								qt:query  	?queryTest 
							] ;
				 mf:result  ?dataOutput .		
		OPTIONAL {
			?testiri mf:action [ qt:data ?dataInput	]							
		}				
		OPTIONAL {
			?testiri mf:action [ qt:graphData ?graphDataInput ]							
		}
		OPTIONAL {
			?testiri mf:action [ qt:serviceData ?serviceDataInput ]							
		}	

    #    BIND(BOUND(?dataInput) AS ?dataInputExist)
    #    BIND(BOUND(?graphDataInput) AS ?graphDataInputExist)
    #    BIND(BOUND(?serviceDataInput) AS ?serviceDataInputExist)
        
        #DISABLE TFT not supports tests with Entailment regime
        OPTIONAL {
            ?testiri mf:action [ sd:EntailmentProfile ?EntailmentProfile ] ;
        }
        OPTIONAL {
            ?testiri mf:action [ sd:entailmentRegime ?entailmentRegime ] ;
        }
        OPTIONAL {
            ?testiri mf:action [ sd:supportedEntailmentProfile ?supportedEntailmentProfile ] ;
        }
        BIND((BOUND(?EntailmentProfile) || BOUND(?entailmentRegime) || BOUND(?supportedEntailmentProfile)) AS ?testSkipped)
	}
}
ORDER BY ?testiri
';

		//echo $q;
		$ENDPOINT->ResetErrors();
		$rows = $ENDPOINT->query($q, 'rows');
		$err = $ENDPOINT->getErrors();

		$iriTest = $GRAPH_RESULTS_EARL."/QueryEvaluationTest/select";
		$iriAssert = $GRAPH_RESULTS_EARL."/QueryEvaluationTest/selectAssert";
		$labelAssert = "Select the QueryEvaluationTest";
		 if ($err) {
			echo "F => Cannot ".$labelAssert;
			$Report->addTestCaseFailure($iriTest,$iriAssert,$labelAssert,print_r($err,true));
			return;
		 }else{
			echo ".";
			$Report->addTestCasePassed($iriTest,$iriAssert,$labelAssert);
		 }

		//Check the nb of tests
		//print_r($rows);
		$nbTest = count($rows["result"]["rows"]);
		echo "Nb tests : ".$nbTest."\n";
		//exit();
		$nbApprovedTests = QueryEvaluationTest::countApprovedTests();

		$iriTest = $GRAPH_RESULTS_EARL."/QueryEvaluationTest/CountTests";
		$iriAssert = $GRAPH_RESULTS_EARL."/QueryEvaluationTest/CountTestsAssert";
		$labelAssert = "Compare the nb of valid tests with the nb of tests in the dataset.";
		if($nbTest !=  $nbApprovedTests ){
//			echo "F";
			echo "NB of tests (".$nbTest."/".$nbApprovedTests ." in theory) is incorrect.\n";
// 		        $Report->addTestCaseFailure($iriTest,$iriAssert,$labelAssert,
// 					"NB of tests (".$nbTest."/".$nbApprovedTests ." in theory) is incorrect.\n TODO//220 but there are tests with several names..."
// 					);
		}else{
//			echo ".";
//			$Report->addTestCasePassed($iriTest,$iriAssert,$labelAssert);
		}



		foreach ($rows["result"]["rows"] as $row){
			$iriTest = trim($row["testiri"]);

			/*
			echo $iriTest;
			//exit();
			if(! preg_match("/exists03/i", $iriTest))
				continue;

			if(! preg_match("/service/i", $iriTest))
				continue;
			*/
			$iriAssertProtocol =$row["testiri"]."/"."Protocol";
			$labelAssertProtocol = trim($row["name"])." : Test the protocol.";
			$iriAssertResponse =$row["testiri"]."/"."Response";
			$labelAssertResponse = trim($row["name"])." : Test the response.";

			if($modeVerbose){
				echo "\n".$iriTest.":".trim($row["name"]).":" ;
			}

			$test = new Test(trim($row["queryTest"]),$iriTest);

            if ($row["testSkipped"]){
                echo "S";
                $messageSkipped = "TFT does not support tests of Entailment.";
                $Report->addTestCaseSkipped($iriTest,$iriAssertProtocol,$labelAssertProtocol,$messageSkipped);
                echo "S";
                $Report->addTestCaseSkipped($iriTest,$iriAssertResponse,$labelAssertResponse,$messageSkipped);
                continue;
            }

            $graphName = "DEFAULT";
            $test->addGraphOutput(trim($row["dataOutput"]),$graphName,$graphName);
            if (array_key_exists("dataInput", $row)){
                $test->addGraphInput(trim($row["dataInput"]),$graphName,$graphName);
            }
            
            if (array_key_exists("graphDataInput", $row)){
                $test->readAndAddMultigraphInput($GRAPHTESTS,$iriTest); 
            }
            if (array_key_exists("serviceDataInput", $row)){
                $test->readAndAddService($GRAPHTESTS,$iriTest);
            }

			$test->doQuery(true,"DEFAULT");
			$err = $test->GetErrors();
			$fail = $test->GetFails();
			if (count($err) != 0) {
                echo "E";//echo "\n".$nameTestQueryFailed." ERROR";
                $Report->addTestCaseError($iriTest,$iriAssertProtocol,$labelAssertProtocol,
                    print_r($err,true));
                echo "S";//echo "\n".$nameTestQueryDataFailed." SKIP";
                $Report->addTestCaseSkipped($iriTest,$iriAssertResponse,$labelAssertResponse,
                "Cannot read result because test:" . $iriAssertProtocol . " is failed."
                );
			}else{
                echo ".";//echo "\n".$nameTestQueryPassed." PASSED";
                $Report->addTestCasePassed($iriTest,$iriAssertProtocol,$labelAssertProtocol);

                if(count($fail) != 0){
                    echo "F";
                    $Report->addTestCaseFailure($iriTest,$iriAssertResponse,$labelAssertResponse,
                        print_r($fail,true));
                }else{
                    echo ".";
                    $Report->addTestCasePassed($iriTest,$iriAssertResponse,$labelAssertResponse,
                        $test->GetTime());
                }
			}
		}
		echo "\n";
	}
}
