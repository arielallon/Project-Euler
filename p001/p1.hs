p1 xs = sum [y | y <- xs, (y `mod` 3 == 0 || y `mod` 5 == 0)]