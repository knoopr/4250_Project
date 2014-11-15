#!/usr/bin/python
import subprocess, re, sys, json

fileName = None
RentalPrices = {}


for argument in sys.argv:
    if argument.endswith(".pdf"):
        fileName = argument

if fileName != None:
    for page in range(3):
        if page%2 == 0:
            HousingType = subprocess.Popen(['pdf2txt', '-p', str(page+3), fileName], stdout=subprocess.PIPE)
            output = HousingType.stdout.read()
            
            if page+3 == 3:
                RentalPrices["type"] = "APARTMENTS";
            else:
                RentalPrices["type"] = "TOWNHOUSES";
            
            regexResult = re.findall("(Toronto [A-Z][0-9]{2})[0-9]*(\$[0-9]{1,2},[0-9]{3}|\-)[0-9]*(\$[0-9]{1,2},[0-9]{3}|\-)[0-9]*(\$[0-9]{1,2},[0-9]{3}|\-)[0-9]*(\$[0-9]{1,2},[0-9]{3}|\-)",output)

            if regexResult != None:
                for regex in regexResult:
                    RentalPrices[regex[0][-3:]] = (regex[1].replace(',',''),regex[2].replace(',',''),regex[3].replace(',',''),regex[4].replace(',',''))
                print json.dumps(RentalPrices, ensure_ascii=False)
                RentalPrices.clear()


