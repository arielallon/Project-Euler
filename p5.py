import time

def idea1(rng, start, jump = 1):
    n = start - jump
    solution = False
    while not solution:
        n += jump
        solution = True
        for i in rng:
            if (n % i) != 0:
                solution = False
                break
    print n, "solution!"

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
    rng = xrange(20,10,-1)
    start = 2520
    #start = 21162959
    t1 = time.time()
    main(2, rng, start)
    t2 = time.time()
    print "took %0.3f ms" % ( (t2-t1)*1000.0)
