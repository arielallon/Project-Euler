'''
Project Euler: Problem 5
Solution by Ariel Allon

"What is the smallest number divisible by each of the numbers 1 to 20?"

Insights:
Can ignore testing 1 - 10 since all numbers divisible by 11 - 20 are also 
divisible by 1 - 10. 
For a number N, every N natural numbers you will find a number divisible by N, 
it makes the most sense to test them in decreasing order since a higher N is 
less likely to be a divisor and thus will shoot down invalid solutions sooner.
'''

import time

# Test every number from start on up whether it's divisible by the numbers in 
# rng. As soon as we encounter a number in rng that is not a divisor, skip to
# n+jump and start at the begining of rng again.
# The simplest method is to set jump to 1, so we test every number, ensureing we
# find the smallest.
# An insight is that if we start at a number divisible by 20, we can set jump to
# 20, since none of the other 19 in between will be divisible by 20 and thus it 
# is pointless to test them.
def idea1(rng, start, jump = 1):
    n = start - jump
    solution = False
    # we have to make it through a full rng of valid divisors for solution
    # to equal True at the while.
    while not solution:
        n += jump
        solution = True
        for i in rng:
            if (n % i) != 0:
                solution = False
                break
    print n, "solution!"

# Another idea of how to approach this. Turns out to be slower, but left for 
# historical purposes. 
def idea2(rng, start):
    curMax = max(rng)
    r = dict([(x,x) for x in rng])
    allEqual = False
    while not allEqual:
        for k,v in r.iteritems():
            if v < curMax:
                allEqual = False
                adjustment = 0 if curMax % k == 0 else 1
                newMax = k * ((curMax/k) + adjustment)
                r[k] = newMax
                curMax = newMax
                break
            else:
                allEqual = True
    print curMax, "solution!"


def main(opt, rng, start = 1):
    if (opt == 1):
        return idea1(rng, start, 1)
    elif (opt == 2):
        return idea1(rng, start, 20)
    elif (opt == 3):
        return idea2(rng, start)


if __name__ == '__main__':
    rng = xrange(20,10,-1)  # range in decreasing order
    start = 2520            # problem defined this as lcm of [1-10]
    t1 = time.time()
    main(2, rng, start)
    t2 = time.time()
    print "took %0.3f ms" % ( (t2-t1)*1000.0)
