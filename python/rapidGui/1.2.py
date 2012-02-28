d= dict (nov=11, oscar=12, papa=13, que=14)
v1 = v2 = d.keys()
sign = '===='
print v1, v2
v1[3] = sign
print v1, v2
print 'keys will return mutable var'
print

v1 = d.keys()
v2 = d.keys()
print v1,  v2
v1[3] = sign
print v1, v2

