"""
  Generator for getting factors for a number
"""
def factor(n):
  yield 1
  i = 2
  limit = n**0.5
  while i <= limit:
    if n % i == 0:
      yield i
      n = n / i
      limit = n**0.5
    else:
      i += 1
  if n > 1:
    yield n

if __name__ == "__main__":
  import sys
  for index in xrange(1,len(sys.argv)):
    print "Factors for %s : %s" %(sys.argv[index], [i for i in factor(int(sys.argv[index]))])
