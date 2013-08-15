<?php

// Our simple hand object. 
// Could have been another layer in the array, but this is a little cleaner.
class hand
{
    public $cards;
    public $ranks;
    
    public function __construct(array $cards)
    {
        $this->cards = $cards;
    }
}


// inits
// translation arrays in reverse, otherwise we'd translate T=>A and all those A=>E
$fromVals = array('A', 'K', 'Q', 'J', 'T'); //, 'J', 'Q', 'K', 'A');
$toVals = array('E', 'D', 'C', 'B', 'A'); //, 'B', 'C', 'D', 'E');

// Used to add uniquness to the hand's cards' array keys, so we don't have duplicates.
// Using floats, instead of e.g. strings, allows us to still get sensible results from a 
// ksort of the cards. Which maps to what is arbitrary.
$suitDecimals = array('C'=>0.1, 'D'=>0.2, 'S'=>0.3, 'H'=>0.4);

/**
 * Used to parse a line from the file into an array of Hex face values alternating with their suits.
 * e.g.:
 * 8C TS KC 9H 4S 7D 2S 5D 3S AC
 *  Array
 *  (
 *      [0] => 8
 *      [1] => C
 *      [2] => 10
 *      [3] => S
 *      [4] => 13
 *      [5] => C
 *      [6] => 9
 *      [7] => H
 *      [8] => 4
 *      [9] => S
 *      [10] => 7
 *      [11] => D
 *      [12] => 2
 *      [13] => S
 *      [14] => 5
 *      [15] => D
 *      [16] => 3
 *      [17] => S
 *      [18] => 14
 *      [19] => C
 *  )
 */
$sscanfFormat = trim(str_repeat('%1X%1s ', 10));

// Used when scoring the round. Sorting descending means we can quit as soon as we have a hit.
$rankCalculations = array(
                            'royalFlush' => 'getRoyalFlush',
                            'straightFlush' => 'getStraightFlush',
                            'fourKind' => 'getFourKind',
                            'fullHouse' => 'getFullHouse',
                            'flush' => 'getFlush',
                            'straight' => 'getStraight',
                            'threeKind' => 'getThreeKind',
                            'twoPairs' => 'getTwoPairs',
                            'onePair' => 'getOnePair',
                            'highCard' => 'getHighCard'
                        );


/**
 * Given an array generated from a row of the file and the sscanf format, builds a hand object.
 * $offset allows us to use a single function and just use the 5 cards starting at the offset.
 * $sorted should always be true, as the rest of the functions rely on this assumption.
 */
function buildHand($roundArr, $offset, $sorted=true)
{
    global $suitDecimals;

    $hand = array();

    // Loop through the 5 face values and suits starting at the $offset and add them to a structured array.
    // We set the key to the face value with the suit added for uniquness (translated to a 
    // decimal so we can still sort).
    $cap = 10+$offset;
    for ($i = 0+$offset; $i < $cap; $i+=2) {
        $key = $roundArr[$i] + $suitDecimals[$roundArr[$i+1]];
        $hand[(string)$key] = array(
                            'number' => $roundArr[$i], 
                            'suit' => $roundArr[$i+1]
                            );
    }
    if (count($hand) != 5) {
        echo "ERR: hand has more or less than 5 cards";
    }
    
    // Sort the hand by its key
    if ($sorted) {
        ksort($hand);   
    }
    return $hand;
}

/**
 * Determine the winner of this round.
 * First we prepare our counts and buckets.
 * Then we loop through the potential ranks in descending order. As soon as we find a 
 * match, we see if only one hand has that rank. If both do, we compare their subranks
 * within the rank, with various tie breaking fallbacks
 */ 
function roundWinner($hand1, $hand2) 
{
    global $rankCalculations;
    
    // Accumulate siblings and buckets
    countSiblings($hand1);
    countSiblings($hand2);
    
    $winner = 0;
    
    // Loop through rank calculations in descending order.
    foreach ($rankCalculations as $rank => $calculation) {

        // See if each hand has this rank
        $rank1 = call_user_func($calculation, $hand1);
        $rank2 = call_user_func($calculation, $hand2);
        
        // If there is more than one hand with this rank
        if (!empty($rank1) && !empty($rank2)) {
            // compare the ranks
            switch ($rank) {
                case 'fourKind':
                    // compare value of quadruplets
                    if (!($winner = compareSiblings($hand1, $hand2, 4))) {
                        // if tie, compare high card
                        $winner = compareHighCards($hand1, $hand2);
                    }
                    break;
                case 'fullHouse':
                    // compare value of triplets
                    if (!($winner = compareSiblings($hand1, $hand2, 3))) {
                        // if tie, compare value of twins
                        $winner = compareSiblings($hand1, $hand2, 2);
                    }
                    break;
                case 'threeKind':
                    // compare value of triplets
                    // if tie, compare high card
                    if (!($winner = compareSiblings($hand1, $hand2, 3))) {
                        // if tie, compare value of twins
                        $winner = compareHighCards($hand1, $hand2);
                    }
                    break;
                case 'twoPairs':
                    // compare value of high set of twins                    
                    if (!($winner = compareSiblings($hand1, $hand2, 2, 1))) {
                        // if tie, compare value of low set of twins
                        if (!($winner = compareSiblings($hand1, $hand2, 2, 0))) {
                            // if tie, compare high card
                            $winner = compareHighCards($hand1, $hand2);
                        }
                    }
                    break;
                case 'onePair':
                    // compare value of twin
                    if (!($winner = compareSiblings($hand1, $hand2, 2, 0))) {
                        // if tie, compare value of twins
                        $winner = compareHighCards($hand1, $hand2);
                    }
                    break;
                case 'royalFlush':
                case 'straightFlush':
                case 'flush':
                case 'straight':
                case 'highCard':
                default:
                    $winner = compareHighCards($hand1, $hand2);
                    break;
            }
            
        }
        // If there is only a single hand with this rank
        elseif (empty($rank1) && !empty($rank2)) {
            $winner = 2;
        }
        elseif (empty($rank2) && !empty($rank1)) {
            $winner = 1;
        }
        
        if ($winner) {
            return $winner;
        }
        // If there are no hands with this rank, let's continue to the next test
    }
    return $winner;
}

/** 
 * Compare two hand's high cards and return the winner or 0 if it's a tie.
 */
function compareHighCards($hand1, $hand2) {
    if (getHighCard($hand1) > getHighCard($hand2)) {
        return 1;
    } 
    elseif (getHighCard($hand2) > getHighCard($hand1)) {
        return 2;
    }
    return 0;
}


/** 
 * Compare the face values of the Nth siblings and return the winner or 0 if it's a tie.
 * Can pass a $hiLo index in the case where there is more than one of those sets of n in each hand.
 */
function compareSiblings($hand1, $hand2, $bucket, $hiLo=0) {
    $sib1 = ($hand1->ranks['buckets'][(string)$bucket][$hiLo]) ? $hand1->ranks['buckets'][(string)$bucket][$hiLo] : null;
    $sib2 = ($hand2->ranks['buckets'][(string)$bucket][$hiLo]) ? $hand2->ranks['buckets'][(string)$bucket][$hiLo] : null;
    if ($sib1 > $sib2) {
        return 1;
    }
    elseif ($sib2 > $sib1) {
        return 2;
    }
    return 0;
}

/** 
 * Get the high card in the hand (by face value);
 */
function getHighCard($hand) {
    if (!isset($hand->ranks['highCard'])) {
        $highCard = end($hand->cards);
        $hand->ranks['highCard'] = $highCard['number'];
    }
    return $hand->ranks['highCard'];
}

/** 
 * Does this hand have one pair?
 * If so, return the face value of a twin.
 */
function getOnePair($hand) {
    if (!isset($hand->ranks['onePair'])) {
        $hand->ranks['onePair'] = (empty($hand->ranks['buckets']['2'])) ? false : end($hand->ranks['buckets']['2']);
    }
    return $hand->ranks['onePair'];
}

/** 
 * Does this hand have two pairs?
 * If so, return an array with the face values of both sets of twins.
 * (Also, might as well set the value for 'onePair', since we have it.)
 */
function getTwoPairs($hand) {
    if (!isset($hand->ranks['twoPair'])) {
    
        if (!empty($hand->ranks['buckets']['2'])) {
            if (count($hand->ranks['buckets']['2']) > 1) {
                $hand->ranks['twoPair'] = $hand->ranks['buckets']['2'];
            }
            $hand->ranks['onePair'] = end($hand->ranks['buckets']['2']);
        }
        
    }
    return $hand->ranks['twoPair'];
}

/** 
 * Does this hand have a three of kind?
 * If so, return the face value of a triplet.
 */
function getThreeKind($hand) {
    if (!isset($hand->ranks['threeKind'])) {
        $hand->ranks['threeKind'] = (empty($hand->ranks['buckets']['3'])) ? false : end($hand->ranks['buckets']['3']);
    }
    return $hand->ranks['threeKind'];
}

/** 
 * Does this hand have a straight?
 * Make sure the cards are monotonically increasing without pleatues -- that is, no siblings.
 * Then, check if the high card face value is 4 above the low card face value.
 */
function getStraight($hand) {
    if (!isset($hand->ranks['straight'])) {
        // if we have an siblings, then we can't have a straight
        $buckets = $hand->ranks['buckets'];
        if (empty($buckets['2']) && empty($buckets['3']) && empty($buckets['4'])) {
        
            // if we had no siblings and the first and last cards are 4 apart, we have a straight
            $firstCard = reset($hand->cards);
            $lastCard = end($hand->cards);
            if ($lastCard['number'] - $firstCard['number'] == 4) {
                return $hand->ranks['straight'] = true;
            }
        }
        $hand->ranks['straight'] = false;
    }
    return $hand->ranks['straight'];
}

/** 
 * Does this hand have a flush?
 * Loop through the suits of the cards in the hand. If any 2 don't match, return false.
 * Otherwise, true.
 */
function getFlush($hand) {
    if (!isset($hand->ranks['flush'])) {
        $firstCard = reset($hand->cards);
        $suit = $firstCard['suit'];
        foreach ($hand->cards as $card) {
            if ($card['suit'] != $suit) {
                return $hand->ranks['flush'] = false;
            }
        }
        $hand->ranks['flush'] = true;
    }
    return $hand->ranks['flush'];
}

/** 
 * Does this hand have a full house?
 * It's just a three of kind and a pair.
 */
function getFullHouse($hand) {
    if (!isset($hand->ranks['fullHouse'])) {
        $threeKind = getThreeKind($hand);
        if (!empty($threeKind)) {
            $onePair = getOnePair($hand);
            if (!empty($onePair)) {
                return $hand->ranks['fullHouse'] = true;
            }
        }
        $hand->ranks['fullHouse'] = false;
    }
    return $hand->ranks['fullHouse'];
}

/** 
 * Does this hand have a four of kind?
 * If so, return the face value of a quadruplet.
 */
function getFourKind($hand) {
    if (!isset($hand->ranks['fourKind'])) {
        $hand->ranks['fourKind'] = (empty($hand->ranks['buckets']['4'])) ? false : end($hand->ranks['buckets']['4']);
    }
    return $hand->ranks['fourKind'];
}

/** 
 * Does this hand have a straight flush?
 * It's just a straight and a flush.
 */
function getStraightFlush($hand) {
    if (!isset($hand->ranks['straightFlush'])) {
        if (getFlush($hand)) {
            if (getStraight($hand)) {
                return $hand->ranks['straightFlush'] = true;
            }
        }
        $hand->ranks['straightFlush'] = false;
    }
    return $hand->ranks['straightFlush'];
}

/** 
 * Does this hand have a royal flush?
 * It's just a straight flush, where the high card is an Ace (14)
 */
function getRoyalFlush($hand) {
    if (!isset($hand->ranks['royalFlush'])) {
        if (getStraightFlush($hand)) {
            if (getHighCard($hand) == 14) {
                return $hand->ranks['royalFlush'] = true;
            }
        }
        $hand->ranks['royalFlush'] = false;
    }
    return $hand->ranks['royalFlush'];
}

/**
 * Step through a hand's cards and count up how many of each number there is.
 * Then, go through that list and bucket the numbers by the count of how many siblings it has.
 */
function countSiblings($hand) {
    $sibs = array();
    $buckets = array();
    
    
    if (!isset($hand->ranks['siblings'])) {
        // tally the count of each card number
        foreach ($hand->cards as $card) {
            $number = (string)$card['number'];
            if (isset($sibs[$number])) {
                $sibs[$number] += 1;
            }
            else {
                $sibs[$number] = 1;
            }
        }
        $hand->ranks['siblings'] = $sibs;
        
        // collate into buckets
        // numbers in each bucket are sorted in ascending order
        foreach ($sibs as $number => $count) {
            if (!isset($buckets[(string)$count])) {
                $buckets[(string)$count] = array();
            }
            $buckets[(string)$count][] = $number;
        }
        $hand->ranks['buckets'] = $buckets;
    }
}


// === main ===
// load file
if (!file_exists('poker.txt') || !is_readable('poker.txt')) {
    die('unable to access file "poker.txt"');
}
$file = fopen('poker.txt', "r");

// read and score lines
$p1Wins = $p2Wins = 0;
while (!feof($file)) {
    $round = fgets($file);
    if (empty($round)) {
        continue;
    }
    
    // translate high-card initials to hex
    $roundHexString = str_replace($fromVals, $toVals, $round);
    
    // split round into array and convert hex values to integers
    $roundArr = sscanf($roundHexString, $sscanfFormat);
    
    // build the hand objects
    $p1Hand = new hand(buildHand($roundArr, 0));
    $p2Hand = new hand(buildHand($roundArr, 10));

    // score hands in parallel
    $winner = roundWinner($p1Hand, $p2Hand);
    if ($winner == 1) {
        $p1Wins++;    
    }
    elseif ($winner == 2) {
        $p2Wins++;
    }
} 
fclose($file);

// problem 54 asked for player 1's total wins
echo $p1Wins; echo "\n";

