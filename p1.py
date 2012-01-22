j = 0
for i in range(3,10000):
	if (i%4 == 0) or (i%3 == 0):
		j += i
print j
