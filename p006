'''
Project Euler: Problem 6
Solution by Ariel Allon

"Find the difference between the sum of the squares of the first one hundred 
natural numbers and the square of the sum."

Insights:
Square of the sum of 1 to N: if N is even, you can sum (1+0 + N-0) + (1+1 + N-1)
 + (1+2 + N-2) + ... + (1+[N/2]-1 + N-[N/2]+1)
Each of those parenthetical sums are equal, and there are N/2 of them, so it's 
the same as (N/2)*(1+N). 
If N is odd, it's ([N-1]/2)*(1+[N-1])+N
'''

def sqrSums(upper):
    if (upper<1):
        return False
    n = upper
    m = 0
    if (upper%2!=0):
        m = n
        n = n-1
    sums = ((n+1)*(n/2))+m
    return sums**2

def sumSqrs(upper):
    total = 0
    for n in xrange(1,upper+1):
        total += n**2
    return total

def main():
    upper = 100
    squareOfSums = sqrSums(upper)
    sumOfSquares = sumSqrs(upper)
    print "square of sums:", squareOfSums
    print "sum of squares:", sumOfSquares
    print "diff:", abs(sumOfSquares - squareOfSums)

if __name__ == "__main__":
    main()
