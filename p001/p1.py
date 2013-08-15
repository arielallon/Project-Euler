'''
Project Euler: Problem 1
Solution by Ariel Allon

"Add all the natural numbers below one thousand that are multiples of 3 or 5."
'''

j = 0
for i in range(3,10000):
	if (i%5 == 0) or (i%3 == 0):
		j += i
print j
