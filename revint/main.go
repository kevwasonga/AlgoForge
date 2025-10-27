package revint
package main

import (
	"fmt"
	"sort"
	"strconv"
)

// getSmallestFromDigits takes an integer input and returns
// the smallest possible number that can be made by rearranging its digits.
// Example: 521 → 125, 907 → 79 (leading zeros are dropped automatically)
func getSmallestFromDigits(num int) int {
	// Step 1: Convert the integer to a string so we can access each digit
	numStr := strconv.Itoa(num)

	// Step 2: Convert the string to a slice of bytes (each byte = one digit character)
	digits := []byte(numStr)

	// Step 3: Sort the digits in ascending order ('0' < '1' < '2' ... '9')
	sort.Slice(digits, func(i, j int) bool {
		return digits[i] < digits[j]
	})

	// Step 4: Convert the sorted slice of digits back to a string
	sortedStr := string(digits)

	// Step 5: Convert the sorted string back into an integer
	// Note: strconv.Atoi automatically ignores leading zeros
	smallestNum, _ := strconv.Atoi(sortedStr)

	return smallestNum
}

func main() {
	num := 521
	smallest := getSmallestFromDigits(num)
	fmt.Println(smallest) // Output: 125
}
