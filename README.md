TFT
===

TFT (Tester for Triplestore) is a script PHP to pass tests through a sparql endpoint.

Usage with jenkins
==================

```
rm -rf TFT 
git clone  --recursive https://github.com/BorderCloud/TFT.git
cd TFT

./tft-testsuite -a -t fuseki -q http://example.com:3030/tests/query -u http://example.com:3030/tests/update 

./tft \
-t fuseki \
-q http://example.com:3030/tests/query \
-u http://example.com:3030/tests/update \
-tt fuseki -tq http://127.0.0.1/ds/query -tu http://127.0.0.1/ds/update \
-o ./junit \
-r ${BUILD_URL} \
--softwareName=Fuseki --softwareDescribeTag=v${VERSIONFUSEKI}  --softwareDescribe="${BUILD_TAG}#${FILEFUSEKI}"

```

Jenkins will be read the reports Junit/XML with this line :

```
TFT/junit/*junit.xml
```