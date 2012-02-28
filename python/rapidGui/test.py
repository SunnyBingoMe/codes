#print "ch 1"
#from sys import *
#print 'argv[0]=', argv[0]
#if (len(argv) > 1):
#	print 'argv[1]=', argv[1]
#
#euro = unichr(8364)
#print 'unichr(8364):',  euro
#
#euro = u'\N{euro sign}'
#print 'uN{euro sign}:',  euro, '; printf: %c' % euro
#
#print 'www.example.com'.strip('cmowe.')

#print "ch 2 . control structure"
#presidents = dict(Wash=(1789,  1797),  Adams=(1797,  1801))
#for key in presidents:
#	print "1.%s: %d-%d." % (key,  presidents[key][0],  presidents[key][1])
#for item in presidents.items():
#	print "2.%s: %d-%d." % (item[0],  item[1][0],  item[1][1])
#for president,  dates in sorted(presidents.items()): #sorted
#	print "3.%s: %d-%d." % (president,  dates[0],  dates[1])

primes = [2]
for x in xrange (2,  50):
	for p in primes:
		if x % p == 0:
			break
	else:
		primes.append(x)
print primes
