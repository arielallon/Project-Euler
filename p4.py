import math

def exclude(r,l):
    if r%10 == 0 or l%10 == 0:
        return False
    return True

def palindrome(n):
    m = int( reverse( str(n) ) )
    return m == int(n)

def reverse(s):
    return s[::-1]

def main():
    start = 999
    used = set([])
    for distance in xrange(0,899):
        half = distance/2.0
        smallHalf = int(math.floor(half))
        bigHalf = int(math.ceil(half))
        
        #if exclude(bigHalf,smallHalf):
         #   continue
        
        while smallHalf >= 0:
            print smallHalf, bigHalf
            signature = ( min(smallHalf, bigHalf), max(smallHalf, bigHalf) )
            if signature not in used:
                used.add(signature)
            
                product = (start - bigHalf) * (start - smallHalf)
                if palindrome(product):
                    print product
                    exit()
                
            smallHalf-=1
            bigHalf+=1


if __name__ == "__main__":
    main()
