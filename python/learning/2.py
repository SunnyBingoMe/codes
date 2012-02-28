oFile = open('SA1.txt', 'r')
print (oFile)

i = 0
sum = 0.0
sumOfSquare = 0.0
max = [0.0, 0.0, 0.0, 0.0, 0.0]
min = 1.0
while True :
	tLine = oFile.readline().strip()
	if tLine:
		i += 1
#		print ('line', i, ':', tLine)
		tLine = float(tLine)
#		print ('line', i, ':', tLine)
		for tMaxIndex in range(0, 5):
			if tLine > max[tMaxIndex]:
				max[tMaxIndex] = tLine
				max.sort()
				break
		if tLine < min:
			min = tLine
		sum += tLine
		sumOfSquare += (tLine**2)
#		print ('sum=', sum)
	else:
		break
print ("count:", i)
print ("sum:", sum)
mean = sum/i
print ("mean:",  mean)
print ("sumOfSquare:", sumOfSquare)
variance = sumOfSquare/i - mean**2
print ("variance:", variance)
print ("std-deviation:", variance**(0.5))
print ("max5:", max)
print ("min: ", min)

