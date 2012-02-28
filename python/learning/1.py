import sys

print ("type in the end number:")
data = sys.stdin.readline()
print "data:", data

endNr = int(data)
endNr += 1
myList = []
for i in range (1,  endNr):
	myList.append (i)

listLength = len (myList)
print ("listLength = %i" % listLength)
for j in range (0,  listLength):
	print ("myList[%d] = " % j, myList[j])
