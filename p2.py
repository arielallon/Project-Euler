fibs = {0:0, 1:1}

def fib(x):
    if x in fibs:
        return fibs[x]
    else:
        y = fib(x-1) + fib(x-2)
        fibs[x] = y
        return y

if __name__ == "__main__":
    total = 0
    i = 0
    curFib = 0
    while curFib <= 4000000:
        curFib = fib(i)
        if curFib % 2 == 0:
            total = total + curFib
        i = i+1
    print total
    
