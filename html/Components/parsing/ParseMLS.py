#!/usr/bin/python
import subprocess, re, sys, json

fileName = None
HousingPrices = {}


for argument in sys.argv:
    if argument.endswith(".pdf"):
        fileName = argument

if fileName != None:
    for page in range(17):
        if page%2 == 0:
            HousingType = subprocess.Popen(['pdf2txt', '-p', str(page+8), fileName], stdout=subprocess.PIPE)
            output = HousingType.stdout.read()
            
            type = re.search("TRANSACTIONS.*,", output)
            if type != None:
                    HousingPrices["type"] = type.group()[12:-1]
            else:
                    HousingPrices["type"] = "DETACHED HOUSES"



            regexResult = re.findall("(Toronto [A-Z][0-9]{2})[0-9]*\$([0-9]{0,3},)?[0-9]{3},[0-9]{3}\$(([0-9]{0,2},)?[0-9]{3},[0-9]{3})\$(([0-9]{0,2},)?[0-9]{3},[0-9]{3})", output)
            
            if regexResult != None:
                for regex in regexResult:
                    HousingPrices[regex[0][-3:]] = (regex[2].replace(',',''),regex[4].replace(',',''))
                print json.dumps(HousingPrices, ensure_ascii=False)
                HousingPrices.clear()



'''
            #West loop
            for i in range(10):
                regexResult = re.search("Toronto W"+ str(i+1).zfill(2) + "[0-9]{1,4}\$([0-9]{0,3},)?[0-9]{3},[0-9]{3}\$([0-9]{0,2},)?[0-9]{3},[0-9]{3}\$([0-9]{0,2},)?[0-9]{3},[0-9]{3}",output)
                
                if regexResult != None:
                    regexSplit = regexResult.group(0).split('$')
                    regexSplit[0] = re.search("[A-Z][0-9]{2}",regexSplit[0]).group(0)
                    print regexSplit



            #Central loop
            for i in range(15):
                regexResult = re.search("Toronto C"+ str(i+1).zfill(2) + "[0-9]{1,4}\$([0-9]{0,3},)?[0-9]{3},[0-9]{3}\$([0-9]{0,2},)?[0-9]{3},[0-9]{3}\$([0-9]{0,2},)?[0-9]{3},[0-9]{3}",output)

                if regexResult != None:
                    regexSplit = regexResult.group(0).split('$')
                    regexSplit[0] = re.search("[A-Z][0-9]{2}",regexSplit[0]).group(0)
                    print regexSplit



            #East Loop
            for i in range(11):
                regexResult = re.search("Toronto E"+ str(i+1).zfill(2) + "[0-9]{1,4}\$([0-9]{0,3},)?[0-9]{3},[0-9]{3}\$([0-9]{0,2},)?[0-9]{3},[0-9]{3}\$([0-9]{0,2},)?[0-9]{3},[0-9]{3}",output)
                
                if regexResult != None:
                    regexSplit = regexResult.group(0).split('$')
                    regexSplit[0] = re.search("[A-Z][0-9]{2}",regexSplit[0]).group(0)
                    print regexSplit
'''

